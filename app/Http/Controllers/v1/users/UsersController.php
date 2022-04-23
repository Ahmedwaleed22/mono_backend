<?php

namespace App\Http\Controllers\v1\users;

use App\Http\Controllers\v1\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
