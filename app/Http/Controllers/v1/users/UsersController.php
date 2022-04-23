<?php

namespace App\Http\Controllers\v1\users;

use App\Http\Controllers\v1\Controller;
use App\Models\User;
use App\Rules\AccountType;
use App\Rules\Gender;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use function response;

class UsersController extends Controller
{
    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->makeHidden(['updated_at', 'id', 'email_verified_at']);

        return response()->json($user);
    }

    public function getUser($username): JsonResponse
    {
        $user = User::where('name', $username)->firstOrFail();
        $user->makeHidden(['fund', 'two_factor_authentication', 'created_at', 'updated_at', 'phone_number', 'email', 'birth_date', 'id', 'email_verified_at']);

        return response()->json($user);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'string|max:255|unique:users,name',
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'avatar' => 'mimes:png,jpg,jpeg',
            'birth_date' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'phone_number' => 'string|max:255',
            'country' => 'string|max:255',
            'city' => 'string|max:255',
            'gender' => ['string', 'max:255', new Gender],
            'profession' => 'string|max:255',
            'account_type' => ['string', 'max:255', new AccountType],
            'password' => 'string|min:8',
        ]);

        $user = $request->user();
        $data = $request->all();

        if ($request->hasFile('avatar')) {
            // Storing File in Amazon s3
            $file = $request->file('avatar');
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = Str::random(32) . '.' . $fileExtension;
            $file->storeAs('user_avatars/', $fileName, 's3');

            $fileLocation = 'https://'. env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/user_avatars/' . $fileName;

            $data['avatar'] = $fileLocation;
        }

        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json($user);
    }
}
