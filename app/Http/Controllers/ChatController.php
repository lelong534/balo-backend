<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Enums\ApiStatusCode;
use App\Events\ChatEvent;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('chat', ['token' => $user->createToken(env('APP_KEY'))->plainTextToken, 'userId' => $user->id]);
    }

    public function fetchAllMessages(Request $request, $userId2)
    {
        return Chat::getMessages($request->user()->id, $userId2);
    }

    public function sendMessage(Request $request, $userId2)
    {
        $chat = new Chat();
        $chat->content = $request->query('content');
        $chat->user_a_id = $request->user()->id;
        $chat->user_b_id = (int)$userId2;
        $chat->save();
        broadcast(new ChatEvent($chat));
        return $chat;
    }

    public function getConversation(Request $request)
    {
        $partnerId = $request->query("partner_id");
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '' || $partnerId == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $count = (int)$count;
        $index = (int)$index;
        $partnerId = (int) $partnerId;
        $chats = Chat::getMessagesFromOneToOne($request->user()->id, $partnerId);
        $chats = array_slice($chats, $count * $index, $count);
        $partner = User::find($partnerId);
        $conversations = [];
        foreach ($chats as $chat) {
            array_push($conversations, [
                "message" => $chat->content,
                "message_id" => $chat->id,
                "unread" => $chat->has_read ? 0 : 1,
                "created" => $chat->created_at,
                "sender" => [
                    "id" => $partner["id"],
                    "username" => $partner["name"],
                    "avatar" => $partner["avatar"]
                ]
            ]);
        }
        return [
            "code" => ApiStatusCode::OK,
            "message" => "OK",
            "data" => [
                "conversation" => $conversations,
                "is_blocked" => $request->user()->isBlockSelf($partnerId)
            ]
        ];
    }

    public function getListConversation(Request $request)
    {
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $count = (int)$count;
        $index = (int)$index;
        $data = [];
        $userId = $request->user()->id;
        $allPartnersID = Chat::getAllPartner($userId);
        foreach ($allPartnersID as $partnerID) {
            $partner = User::find($partnerID);
            $chats = Chat::getMessages($userId, $partnerID);
            $lastMessage = array_pop($chats);
            array_push($data, [
                "id" => $lastMessage->id,
                "partner" => [
                    "id" => $partnerID,
                    "username" => $partner["name"],
                    "avatar" => $partner["avatar"],
                ],
                "lastmessage" => [
                    "message" => $lastMessage->content,
                    "created" => $lastMessage->created_at,
                    "unread" => $lastMessage->has_read ? 0 : 1
                ]
            ]);
        }
        $data = array_slice($data, $count * $index, $count);
        $numberNewMessage = DB::table('chats')
            ->where('has_read', '=', false)
            ->where('user_a_id', '=', $userId)
            ->orWhere('user_b_id', '=', $userId)
            ->count();
        return [
            "code" => ApiStatusCode::OK,
            "message" => "OK",
            "data" => $data,
            "numNewMessage" => $numberNewMessage
        ];
    }

    public function setReadMessage(Request $request)
    {
        $partnerId = $request->query("partner_id");
        $chatId = $request->query("conversation_id");
        if ($partnerId == '' || $chatId == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $partnerId = (int)$partnerId;
        $chatId = (int)$chatId;
        $partner = User::find($partnerId);
        if ($partner == null) {
            return [
                "code" => 9994,
                "message" => "User not found"
            ];
        }
        $chat = Chat::find($chatId);
        if ($chat == null) {
            return [
                "code" => 9994,
                "message" => "Conversation not found"
            ];
        }
        if ($chat->belongTo($request->user()->id, $partnerId)) {
            $chat->has_read = true;
            $chat->save();
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => $chat->content,
            ];
        } else {
            return [
                "code" => ApiStatusCode::NOT_EXISTED,
                "message" => "Tin nhắn Id ".$chatId." không thuộc của userId ".$request->user()->id." và userId ".$partnerId
            ];
        }
    }

    public function deleteMessage(Request $request)
    {
        $partnerId = $request->query("partner_id");
        $chatId = $request->query("message_id");
        if ($partnerId == '' || $chatId == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $partnerId = (int)$partnerId;
        $chatId = (int)$chatId;
        $chat = Chat::find($chatId);
        if ($chat == null) {
            return [
                "code" => 9994,
                "message" => "Conversation not found"
            ];
        }
        if ($chat->belongTo($request->user()->id, $partnerId)) {
            $chat->delete();
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK"
            ];
        } else {
            return [
                "code" => ApiStatusCode::NOT_EXISTED,
                "message" => "Tin nhắn Id ".$chatId." không thuộc của userId ".$request->user()->id." và userId ".$partnerId
            ];
        }

    }

    public function deleteConversation(Request $request)
    {
        $partnerId = $request->query("partner_id");
        $chatId = $request->query("conversation_id");
        if ($partnerId == '' || $chatId == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $partnerId = (int)$partnerId;
        $chatId = (int)$chatId;
        $partner = User::find($partnerId);
        if ($partner == null) {
            return [
                "code" => 9994,
                "message" => "User not found"
            ];
        }
        $chat = Chat::find($chatId);
        if ($chat == null) {
            return [
                "code" => 9994,
                "message" => "Conversation not found"
            ];
        }
        if ($chat->belongTo($request->user()->id, $partnerId)) {
            Chat::deleteMessages($request->user()->id, $partnerId);
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK"
            ];
        } else {
            return [
                "code" => ApiStatusCode::NOT_EXISTED,
                "message" => "Tin nhắn Id ".$chatId." không thuộc của userId ".$request->user()->id." và userId ".$partnerId
            ];
        }

    }
}
