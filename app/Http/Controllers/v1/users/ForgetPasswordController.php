<?php

namespace App\Http\Controllers\v1\users;

use App\Http\Controllers\v1\Controller;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use function response;

class ForgetPasswordController extends Controller
{
    public function forgetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'email|required'
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        if ($this->sendResetEmail($user->email, $token)) {
            return response()->json([
                'success' => true,
                'message' => 'A reset link has been sent to your email address.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Couldn\'t send email.'
            ], 500);
        }
    }

    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'token' => 'string|required',
            'password' => 'string|required|min:8',
            'logout_all_devices' => 'boolean'
        ]);

        $passwordReset = DB::table('password_resets')->where('token', $request->token)->first();

        $user = User::where('email', $passwordReset->email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->save();

        if ($request->logout_all_devices) {
            $user->tokens()->delete();
        }

        DB::table('password_resets')->where('token', $request->token)->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->respondWithToken($token);
    }

    private function sendResetEmail($email, $token): bool
    {
        try {
            Mail::to($email)->send(new ForgetPasswordMail($token));

            return true;
        } catch (\Exception $e) {
            return false;
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
