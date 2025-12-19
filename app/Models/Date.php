<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'meeting_date',
        'is_flexible',
        'address',
        'latitude',
        'longitude',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'is_flexible'  => 'boolean',
        'latitude'     => 'float',
        'longitude'    => 'float',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
