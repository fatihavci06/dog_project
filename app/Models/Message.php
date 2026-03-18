<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'receiver_id', 'body', 'attachments', 'status', 'delivered_at', 'read_at'];

    protected $casts = [
        'attachments' => 'array',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
   protected function createdAt(): Attribute
{
    return Attribute::make(
        get: fn ($value) => Carbon::parse($value)->toIso8601ZuluString()
    );
}

protected function updatedAt(): Attribute
{
    return Attribute::make(
        get: fn ($value) => Carbon::parse($value)->toIso8601ZuluString()
    );
}
}
