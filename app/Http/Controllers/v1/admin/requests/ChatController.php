<?php

namespace App\Http\Controllers\v1\admin\requests;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function chatRooms(): JsonResponse
    {
        $chatRooms = ChatRoom::all();
        return response()->json($chatRooms);
    }

    public function chatMessages($id): JsonResponse
    {
        $chatMessages = ChatMessage::with('user')->where('chat_room_id', $id)->get();
        return response()->json($chatMessages);
    }
}
