<?php

namespace Ekremogul\LaravelMessaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use phpDocumentor\Reflection\Types\Boolean;

class Conversation extends Model
{
    protected $table = "laravel_messaging_conversations";

    protected $fillable = [
        "user_one",
        "user_two",
        "status"
    ];

    protected $casts = [
        "status" => "bool"
    ];

    public function messages() : HasMany
    {
        return $this->hasMany(Message::class, "conversation_id", "id");
    }

    public function userone(): BelongsTo
    {
        return $this->belongsTo(config('messaging.user_model', 'App\Models\User'), 'user_one', 'id');
    }
    public function usertwo(): BelongsTo
    {
        return $this->belongsTo(config('messaging.user_model', 'App\Models\User'), 'user_two', 'id');
    }

    public function exitsById($id): bool
    {
        $conversation = Conversation::find($id);
        return (bool)$conversation;
    }

    public function checkUsersExists($user1, $user2): int|Boolean
    {
        $conversation = Conversation::query()
            ->where(function($query) use ($user1, $user2) {

            });
    }
}
