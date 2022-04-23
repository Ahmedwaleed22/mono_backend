<?php

use App\Http\Controllers\v1\funds\DepositController;
use App\Http\Controllers\v1\funds\WithdrawsController;
use App\Http\Controllers\v1\requests\ChatController;
use App\Http\Controllers\v1\requests\ProjectsController;
use App\Http\Controllers\v1\requests\RequestsController;
use App\Http\Controllers\v1\services\ServicesController;
use App\Http\Controllers\v1\services\SubServicesController;
use App\Http\Controllers\v1\users\AuthController;
use App\Http\Controllers\v1\users\FollowingController;
use App\Http\Controllers\v1\users\ForgetPasswordController;
use App\Http\Controllers\v1\users\UsersController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth',
], function() {
    Route::post('/register', [AuthController::class, 'register']);
    Route::group([
        'prefix' => 'login'
    ], function() {
        Route::post('/', [AuthController::class, 'login']);
        Route::post('/verify', [AuthController::class, 'verify_login']);
    });
});

Route::group([
    'prefix' => 'forget-password',
], function() {
    Route::post('/', [ForgetPasswordController::class, 'forgetPassword']);
    Route::post('/reset', [ForgetPasswordController::class, 'resetPassword']);
});

Route::group([
   'prefix' => 'users',
], function() {
    Route::middleware('auth:sanctum')->get('/me', [UsersController::class, 'getCurrentUser']);
    Route::get('/{username}', [UsersController::class, 'getUser']);
});

Route::group([
    'prefix' => 'services',
], function() {
    Route::get('/primary', [ServicesController::class, 'getPrimaryServices']);
    Route::get('/', [ServicesController::class, 'getAllServices']);
    Route::get('/{service}', [SubServicesController::class, 'getByService']);
//    Route::group([
//        'middleware' => 'auth:sanctum'
//    ], function() {
//        Route::post('/', [ServicesController::class, 'createService']);
//    });
});

Route::group([
   'prefix' => 'sub-services',
], function() {
    Route::get('/', [SubServicesController::class, 'all']);
    Route::middleware('auth:sanctum')->post('/', [SubServicesController::class, 'createSubService']);
    Route::middleware('auth:sanctum')->get('/user-interests', [SubServicesController::class, 'userInterests']);
    Route::get('/{subService}', [SubServicesController::class, 'getSubService']);
});

Route::group([
   'prefix' => 'deposits',
], function() {
    Route::middleware('auth:sanctum')->post('/', [DepositController::class, 'fund']);
    Route::get('/callback', [DepositController::class, 'callback']);
});

Route::group([
    'prefix' => 'chats',
    'middleware' => 'auth:sanctum'
], function() {
    Route::get('/', [ChatController::class, 'chatRooms']);
    Route::get('/rooms/{roomID}/messages', [ChatController::class, 'chatMessages']);
    Route::post('/rooms/{roomID}/messages', [ChatController::class, 'newMessage']);
});

Route::group([
    'prefix' => 'requests',
    'middleware' => 'auth:sanctum'
], function() {
    Route::post('/{serviceSlug}/request', [RequestsController::class, 'requestService']);
    Route::group([
       'prefix' => 'milestones',
    ], function() {
        Route::get('/{serviceRequestID}', [RequestsController::class, 'serviceMilestones']);
        Route::post('/{serviceRequestID}', [RequestsController::class, 'createMilestone']);
        Route::post('/{milestoneID}/release', [RequestsController::class, 'releaseMilestone']);
    });
});

Route::group([
    'prefix' => 'followings'
], function() {
    Route::get('/{username}', [FollowingController::class, 'followings']);

    Route::group([
        'middleware' => 'auth:sanctum'
    ], function() {
        Route::post('/follow/{userID}', [FollowingController::class, 'follow']);
        Route::delete('/follow/{userID}', [FollowingController::class, 'unFollow']);
    });
});

Route::group([
   'prefix' => 'projects',
], function() {
    Route::group([
        'middleware' => 'auth:sanctum'
    ], function() {
        Route::post('/{requestID}', [ProjectsController::class, 'createProject']);
        Route::get('/like/{projectID}', [ProjectsController::class, 'likeProject']);
    });
    Route::get('/{username}', [ProjectsController::class, 'userProjects']);
});

Route::group([
    'prefix' => 'withdraw',
    'middleware' => 'auth:sanctum'
], function() {
    Route::get('/', [WithdrawsController::class, 'withdraws']);
    Route::post('/', [WithdrawsController::class, 'withdraw']);
});
