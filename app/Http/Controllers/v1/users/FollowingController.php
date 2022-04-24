<?php

namespace App\Http\Controllers\v1\users;

use App\Http\Controllers\v1\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use function response;

class FollowingController extends Controller
{
    public function followings(Request $request, $username): \Illuminate\Http\JsonResponse
    {
        $user = User::with('followings', 'followers')->where('name', $username)->firstOrFail();
        return response()->json($user);
    }

    public function follow(Request $request, $userID): \Illuminate\Http\JsonResponse
    {
        $follow = $request->user()->followings()->syncWithoutDetaching($userID);

        if (count($follow['attached']) > 0) {
            return response()->json([
                'message' => trans('follow.followed')
            ], 201);
        }

        return response()->json([
            'message' => trans('follow.already_following')
        ], 409);
    }

    public function unFollow(Request $request, $userID): \Illuminate\Http\JsonResponse
    {
        $follow = $request->user()->followings()->detach($userID);

        if ($follow) {
            return response()->json([
                'message' => trans('follow.unfollowed')
            ], 200);
        }

        return response()->json([
            'message' => trans('follow.not_following')
        ], 409);
    }
}
