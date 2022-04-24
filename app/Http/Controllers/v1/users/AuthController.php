<?php

namespace App\Http\Controllers\v1\users;

use App\Http\Controllers\v1\Controller;
use App\Mail\login2FAMail;
use App\Models\TwoFactorAuth;
use App\Models\User;
use App\Rules\AccountType;
use App\Rules\Gender;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use function response;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,name',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'avatar' => 'mimes:png,jpg,jpeg',
            'birth_date' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'gender' => ['required', 'string', 'max:255', new Gender],
            'profession' => 'required|string|max:255',
            'account_type' => ['required', 'string', 'max:255', new AccountType],
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request->username;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->birth_date = $request->birth_date;
        $user->email = $request->email;
        $user->phone_number = $request->phone_number;
        $user->country = $request->country;
        $user->city = $request->city;
        $user->gender = $request->gender;
        $user->profession = $request->profession;
        $user->account_type = $request->account_type;
        $user->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            // Storing File in Amazon s3
            $file = $request->file('avatar');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::random(32) . '.' . $fileExtension;
            $file->storeAs('user_avatars/', $fileName, 's3');

            $fileLocation = 'https://'. env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/user_avatars/' . $fileName;

            $user->avatar = $fileLocation;
        }

        $user->save();

        if ($request->account_type === "client") {
            $user->assignRole('client');
        } else if ($request->account_type === "worker") {
            $user->assignRole('worker');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->respondWithToken($token);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => trans('auth.failed')
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        if ($user->two_factor_authentication) {
            $code = rand(10000, 99999);

            $twoFactorAuth = new TwoFactorAuth();
            $twoFactorAuth->code = $code;
            $twoFactorAuth->user_id = $user->id;
            $twoFactorAuth->save();

            $this->send2faCode($user->email, $code);

            return response()->json([
                'two_factor_auth' => true,
                'message' => trans('auth.code_send')
            ]);
        } else {
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'two_factor_auth' => false,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }
    }

    public function verify_login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'email|required',
            'code' => 'string|required'
        ]);

        $user = User::where('email', '=', $request->email)->first();
        $twoFactorAuth = TwoFactorAuth::where('user_id', '=', $user->id)->first();

        if ($user && $twoFactorAuth->code == $request->code && $twoFactorAuth->expiry_date < Carbon::now()->addMinutes(15)) {
            $twoFactorAuth->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    private function send2faCode($email, $code): void
    {
        try {
            Mail::to($email)->send(new login2FAMail($code));

            return;
        } catch (\Exception $e) {
            return;
        }
    }

    private function respondWithToken($token): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
