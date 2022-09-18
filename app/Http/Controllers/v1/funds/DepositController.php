<?php

namespace App\Http\Controllers\v1\funds;

use App\Http\Controllers\v1\Controller;
use App\Mail\DepositMadeMail;
use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use function env;
use function response;

class DepositController extends Controller
{
    public function fund(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:50'
        ]);

        $token = $this->getToken();
        $order = $this->getOrder($token, $request->amount, $request->user());
        $paymentToken = $this->getPaymentToken($order, $token, $request->amount, $request->user());

        return response()->json([
            'payment_iframe' => 'https://accept.paymob.com/api/acceptance/iframes/369624?payment_token=' . $paymentToken
        ]);
    }

    private function getToken() {
        $response = Http::post('https://accept.paymob.com/api/auth/tokens', [
            'api_key' => env('PAYMOB_API_KEY')
        ]);

        return $response->object()->token;
    }

    private function getOrder($token, $amount, $user): object
    {
        $data = [
            'auth_token' => $token,
            'delivery_needed' => 'false',
            'amount_cents' => $amount * 100,
            "currency" => "EGP",
            'items' => []
        ];

        $response = Http::post('https://accept.paymob.com/api/ecommerce/orders', $data);

        $fund = new Fund;
        $fund->fund_id = $response->object()->id;
        $fund->amount = $amount;
        $fund->user_id = $user->id;
        $fund->save();

        return $response->object();
    }

    private function getPaymentToken($order, $token, $amount, $user) {
        $billingData = [
            'apartment' => 'NA',
            'email' => $user->email,
            'floor' => 'NA',
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'street' => 'NA',
            'building' => 'NA',
            'phone_number' => $user->phone_number,
            'shipping_method' => 'NA',
            'postal_code' => 'NA',
            'city' => $user->city,
            'country' => $user->country,
            'state' => 'NA'
        ];

        $data = [
            'auth_token' => $token,
            'amount_cents' => $amount * 100,
            'expiration' => 3600,
            'order_id' => $order->id,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => env('PAYMOB_INTEGRATION_ID')
        ];

        $response = Http::post('https://accept.paymob.com/api/acceptance/payment_keys', $data);

        return $response->object()->token;
    }

    public function callback(Request $request) {
        $data = $request->all();
        ksort($data);

        $hmac = $data['hmac'];
        $hmacKeys = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success',
        ];

        $connectedString = '';

        foreach ($data as $key => $element) {
            if (in_array($key, $hmacKeys)) {
                $connectedString .= $element;
            }
        }

        $secret = env('PAYMOB_HMAC');
        $hashed = hash_hmac('sha512', $connectedString, $secret);


        $orderID = $data['order'];
        $fund = Fund::with('user')->where('fund_id', $orderID)->firstOrFail();

        if ($hashed == $hmac) {
            $amount = $data['amount_cents'] / 100;

            if ($fund->status == 'pending') {
                $fund->user->funds += $amount;
                $fund->status = 'completed';
                $fund->save();
                $fund->user->save();

                $this->sendDepositMadeMail($fund->user->email, $amount);

                return Redirect::away(env('PAYMENT_SUCCESS_URL'));
            }
        }

        $fund->status = 'failed';
        $fund->save();

        return Redirect::away(env('PAYMENT_FAILED_URL'));
    }

    private function sendDepositMadeMail($email, $amount): void
    {
        try {
            Mail::to($email)->send(new DepositMadeMail($amount));

            return;
        } catch (\Exception $e) {
            return;
        }
    }
}
