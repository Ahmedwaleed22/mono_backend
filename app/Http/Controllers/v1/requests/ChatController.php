<?php

namespace App\Http\Controllers\v1\requests;

use App\Events\NewChatMessage;
use App\Http\Controllers\v1\Controller;
use App\Models\ChatMessage;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use function broadcast;
use function response;

class ChatController extends Controller
{
    public function chatRooms(Request $request): JsonResponse
    {
        $user = $request->user();
        $serviceRequests = ServiceRequest::with('chatRoom', 'client', 'worker')->where('client_id', $user->id)->orWhere('worker_id', $user->id)->get();

        foreach ($serviceRequests as $serviceRequest) {
            $serviceRequest->makeHidden(['client', 'worker']);

            if ($user->id == $serviceRequest->client_id) {
                $serviceRequest->user = $serviceRequest->worker;
            } else {
                $serviceRequest->user = $serviceRequest->client;
            }
        }

        return response()->json($serviceRequests);
    }

    public function chatMessages(Request $request, $roomID): JsonResponse
    {
        $user = $request->user();
        $chatRooms = ServiceRequest::with('chatRoom')->where('client_id', $user->id)->orWhere('worker_id', $user->id)->get();
        $chatRooms->pluck('chatRoom')->where('id', $roomID)->firstOrFail();

        $messages = ChatMessage::where('chat_room_id', $roomID)->with('user')->orderBy('created_at', 'DESC')->get();

        return response()->json($messages);
    }

    public function newMessage(Request $request, $roomID): JsonResponse
    {
        $request->validate([
           'message' => 'required|string',
        ]);

        $user = $request->user();
        $chatRooms = ServiceRequest::with('chatRoom')->where('client_id', $user->id)->orWhere('worker_id', $user->id)->get();
        $chatRooms->pluck('chatRoom')->where('id', $roomID)->firstOrFail();

        $message = new ChatMessage;
        $message->message = $request->message;
        $message->user_id = $user->id;
        $message->chat_room_id = $roomID;
        $message->save();

        broadcast(new NewChatMessage($message));

        return response()->json($message);
    }
}
