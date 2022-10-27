<?php

namespace Ekremogul\LaravelMessaging\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $table = "laravel_messaging_messages";

    protected $fillable = [
        "message",
        "is_seen",
        "archived_from_sender",
        "archived_from_receiver",
        "user_id",
        "conversation_id",
    ];

    protected $casts = [
        "is_seen" => "bool",
        "archived_from_sender" => "bool",
        "archived_from_receiver" => "bool",
    ];

    public function conversation() : BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('messaging.user_model', '\App\Models\User'));
    }

    public function sender()
    {
        return $this->user();
    }
}
