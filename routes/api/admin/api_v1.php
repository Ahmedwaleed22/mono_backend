<?php

use \App\Http\Controllers\v1\admin\users\UsersController;
use \App\Http\Controllers\v1\admin\services\ServicesController;
use \App\Http\Controllers\v1\admin\services\SubServicesController;
use \App\Http\Controllers\v1\admin\funds\WithdrawsController;
use \App\Http\Controllers\v1\admin\requests\ChatController;
use \App\Http\Controllers\v1\admin\requests\ProjectsController;
use \App\Http\Controllers\v1\admin\requests\RequestsController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/users', UsersController::class);
Route::apiResource('/services', ServicesController::class);
Route::apiResource('/sub-services', SubServicesController::class);
Route::apiResource('/projects', ProjectsController::class, ['except' => ['store', 'update']]);

Route::group([
    'prefix' => 'withdraws'
], function() {
    Route::get('/', [WithdrawsController::class, 'index']);
    Route::get('/{id}', [WithdrawsController::class, 'show']);
    Route::post('/{id}/complete', [WithdrawsController::class, 'setCompleted']);
    Route::post('/{id}/refuse', [WithdrawsController::class, 'setRefused']);
});

Route::group([
    'prefix' => 'chats'
], function() {
    Route::get('/', [ChatController::class, 'chatRooms']);
    Route::get('/{id}', [ChatController::class, 'chatMessages']);
});

Route::group([
    'prefix' => 'requests'
], function() {
    Route::get('/', [RequestsController::class, 'index']);
    Route::get('/{id}', [RequestsController::class, 'show']);
    Route::post('/milestone/{id}/release', [RequestsController::class, 'releaseMilestone']);
    Route::post('/milestone/{id}/refund', [RequestsController::class, 'refundMilestone']);
});
