<?php

namespace Ekremogul\LaravelMessaging;

use Ekremogul\LaravelMessaging\Models\Conversation;
use Ekremogul\LaravelMessaging\Models\Message;
use Illuminate\Http\Response;

class LaravelMessaging
{
    protected int $authUser;
    protected bool $filteredMessage;
    protected $filterChecks = [];
    protected $multiCharReplace = false;
    protected $replaceWithLength;
    protected $replaceWithText;
    protected $replaceFullWords = true;
    protected $strReplace = [
        'a' => '(a|a\.|a\-|4|@|Á|á|À|Â|à|Â|â|Ä|ä|Ã|ã|Å|å|α|Δ|Λ|λ)',
        'b' => '(b|b\.|b\-|8|\|3|ß|Β|β)',
        'c' => '(c|c\.|c\-|Ç|ç|¢|€|<|\(|{|©)',
        'd' => '(d|d\.|d\-|&part;|\|\)|Þ|þ|Ð|ð)',
        'e' => '(e|e\.|e\-|3|€|È|è|É|é|Ê|ê|∑)',
        'f' => '(f|f\.|f\-|ƒ)',
        'g' => '(g|g\.|g\-|6|9)',
        'h' => '(h|h\.|h\-|Η)',
        'i' => '(i|i\.|i\-|!|\||\]\[|]|1|∫|Ì|Í|Î|Ï|ì|í|î|ï)',
        'j' => '(j|j\.|j\-)',
        'k' => '(k|k\.|k\-|Κ|κ)',
        'l' => '(l|1\.|l\-|!|\||\]\[|]|£|∫|Ì|Í|Î|Ï)',
        'm' => '(m|m\.|m\-)',
        'n' => '(n|n\.|n\-|η|Ν|Π)',
        'o' => '(o|o\.|o\-|0|Ο|ο|Φ|¤|°|ø)',
        'p' => '(p|p\.|p\-|ρ|Ρ|¶|þ)',
        'q' => '(q|q\.|q\-)',
        'r' => '(r|r\.|r\-|®)',
        's' => '(s|s\.|s\-|5|\$|§)',
        't' => '(t|t\.|t\-|Τ|τ)',
        'u' => '(u|u\.|u\-|υ|µ)',
        'v' => '(v|v\.|v\-|υ|ν)',
        'w' => '(w|w\.|w\-|ω|ψ|Ψ)',
        'x' => '(x|x\.|x\-|Χ|χ)',
        'y' => '(y|y\.|y\-|¥|γ|ÿ|ý|Ÿ|Ý)',
        'z' => '(z|z\.|z\-|Ζ)',
    ];
    public static function create(): static
    {
        return new static();
    }

    public function __construct()
    {
        $this->filteredMessage = config("messaging.filter.enable", true);
        $this->authUser = auth()->id();
        $this->replaceWithText = config("messaging.filter.bad_word.replace_with");
        $this->generateFilterChecks();;
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
            $firstMessage = $item->messages->first();
            if($this->filteredMessage){
                $firstMessage->message = $this->checkBadWords($firstMessage->message);
            }

            $newItem->message = $firstMessage;
            $newItem->withUser = $conversationWith;
            return $newItem;
        });
    }

    public function getMessagesWithUser($user_id, $offset = 0, $take = 20)
    {
        $conversation_id = $this->checkUsersExists($user_id, $this->authUser);

        $conversation = Conversation::query()
            ->findOrFail($conversation_id);

        $messages = $conversation
            ->messages()
            ->with('sender')
            ->offset($offset)
            ->take($take)
            ->orderByDesc('id')
            ->get()
            ->reverse()->map(function($item) {
                if ($this->filteredMessage) {
                    $item->message = $this->checkBadWords($item->message);
                }
                return $item;
            });
        return $messages;
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
                ->where('conversation_id', $conversation_id)
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
    public function disableFilter()
    {
        $this->filteredMessage = false;
        return $this;
    }
    private function checkBadWords($message){
        if( !is_string($message) || !trim($message))
            return $message;

        if (config("messaging.filter.enable") && config("messaging.filter.email_filtered.enable")){
            $matches = [];
            $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
            preg_match_all($pattern, $message, $matches);
            if (count($matches[0])) {
                $message = str($message)->replace($matches[0], config("messaging.filter.email_filtered.replace_with"));
            }
        }
        $message = $this->filterMessage($message);
        return $message;
    }

    private function generateFilterChecks()
    {
        $this->filterChecks = [];
        foreach(config("messaging.filter.bad_word.list", []) as $string ) {
            $this->filterChecks[] = $this->getFilterRegexp($string);
        }
    }
    private function getFilterRegexp($string) {
        $replaceFilter = $this->replaceFilter($string);

        if ($this->replaceFullWords) {
            return '/\b'.$replaceFilter.'\b/iu';
        }

        return '/'.$replaceFilter.'/iu';
    }
    private function replaceFilter($string)
    {
        $this->replaceWith($this->replaceWithText);

        return str_ireplace(array_keys($this->strReplace), array_values($this->strReplace), $string);
    }
    private function filterMessage($message)
    {
        return preg_replace_callback($this->filterChecks, function($matches) {
            return $this->replaceWithFilter($matches[0]);
        }, $message);
    }
    public function replaceWith($string)
    {
        $this->replaceWith = $string;

        $this->replaceWithLength = mb_strlen($this->replaceWith);

        $this->multiCharReplace = $this->replaceWithLength === 1;

        return $this;
    }

    private function replaceWithFilter($message)
    {
        $string_length = mb_strlen($message);

        if($this->multiCharReplace) {
            return str_repeat($this->replaceWithText, $string_length);
        }
        return $this->randomFilterChar($string_length);
    }
    private function randomFilterChar($len)
    {
        $len = $len == 0 ? 1 : $len;
        $length = 0;
        try {
            $length = str_shuffle(
                str_repeat($this->replaceWithText,
                    intval($len / $this->replaceWithLength)
                )
                .substr($this->replaceWithText, 0, ($len % $this->replaceWithLength))
            );
        }catch (\Exception $e){
            dd($e);
        }
        return $length;
    }
}
