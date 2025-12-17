<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status'
    ];

    public function sender()
    {
        return $this->belongsTo(PupProfile::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(PupProfile::class, 'receiver_id', 'id');
    }

}
