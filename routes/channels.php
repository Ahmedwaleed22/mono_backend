<?php

use Illuminate\Support\Facades\Broadcast;
use \Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{roomID}', function ($user, $roomID) {
    $chatRoom = \App\Models\ChatRoom::with('serviceRequest')->where('id', $roomID)->first();

    if ($chatRoom->serviceRequest()->client_id == $user->id || $chatRoom->serviceRequest()->worker_id == $user->id) {
        return true;
    }

    return false;
});
