<?php

namespace Ekremogul\LaravelMessaging;

use Ekremogul\LaravelMessaging\Models\Conversation;
use Ekremogul\LaravelMessaging\Models\Message;
use Illuminate\Http\Response;

class LaravelMessaging
{
    protected int $authUser;

    public static function create(): static
    {
        return new static();
    }

    public function __construct()
    {
        $this->authUser = auth()->id();
    }

    public function checkUsersExists($user1, $user2): int|bool
    {
        $conversation = Conversation::query()
            ->where(function($query) use ($user1, $user2) {
                $query->where(
                    function ($q) use ($user1, $user2) {
                        $q->where('user_one', $user1)
                            ->where('user_two', $user2);
                    }
                )
                    ->orWhere(
                        function ($q) use ($user1, $user2) {
                            $q->where('user_one', $user2)
                                ->where('user_two', $user1);
                        }
                    );
            })->first();
        return (bool)$conversation ? $conversation->id : false;
    }

    public function inbox($order = "desc", $offset = 0, $take = 20)
    {
        $conversation = new Conversation();
        $conversation->authUser = $this->authUser;
        $user = $this->authUser;
        $messageThread = $conversation->with(
            [
                "messages" => function ($query) use($user) {
                    return $query->where(
                        function ($query) use ($user) {
                            $query->where('user_id', $user)
                                ->where('archived_from_sender', 0);
                        }
                    )
                        ->orWhere(
                            function ($query) use ($user) {
                                $query->where('user_id', '!=', $user)
                                    ->where('archived_from_receiver', 0);
                            }
                        )->orderByDesc('id');
                }, "messages.sender", "userone", "usertwo"
            ]
        )
            ->where('user_one', $user)
            ->orWhere("user_two", $user)
            ->offset($offset)
            ->take($take)
            ->orderBy("updated_at", $order)
            ->get();

        return $messageThread->map(function ($item) use ($user){
            $conversationWith = ($item->userone->id == $user) ? $item->usertwo : $item->userone;
            $newItem = (object)null;
            $unreded_message = $item->messages()->where("user_id", "!=", $this->authUser)->where('is_seen',0)->count();
            $newItem->unreaded_message = $unreded_message ? 1 : 0;
            $newItem->total_unread = $unreded_message;
            $newItem->message = $item->messages->first();
            $newItem->withUser = $conversationWith;
            return $newItem;
        });
    }

    public function getMessagesWithUser($user_id, $offset = 0, $take = 20)
    {
        $conversation_id = $this->checkUsersExists($user_id, $this->authUser);

        $conversation = Conversation::query()
            ->findOrFail($conversation_id);

        return $conversation->messages()->with('sender')->offset($offset)->take($take)->orderByDesc('id')->get()->reverse();
    }

    public function sendMessage($receiver_id, $message)
    {
        $conversation_id = $this->checkUsersExists($this->authUser, $receiver_id);
        if(!$conversation_id){
            $conversation = Conversation::query()
                ->create([
                    "user_one" => $this->authUser,
                    "user_two" => $receiver_id,
                    "status" => 1
                ]);
            $conversation_id = $conversation->id;
        }

        $message = Message::create([
            "message" => $message,
            "conversation_id" => $conversation_id,
            "user_id" => $this->authUser,
            "is_seen" => 0
        ]);
        $message->conversation->touch();

        return $message;
    }

    public function makeSeen($messageId)
    {
        $seen = Message::query()
            ->findOrFail($messageId)
            ->update([
                'is_seen' => 1
            ]);
        return (bool)$seen;
    }

    public function makeSeenAll($user_id)
    {
        $conversation_id = $this->checkUsersExists($this->authUser, $user_id);

        if ($conversation_id) {
            $ids = [];
            $messages = Message::query()
                ->where('user_id', '!=', $this->authUser)
                ->where('is_seen', 0)
                ->update([
                    "is_seen" => 1
                ]);
            return true;
        }
        return false;
    }

    public function getUnreadInboxCount()
    {
        return $this->inbox()->sum("unreaded_message");
    }
}
