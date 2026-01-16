<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'color',
        'location',
        'latitude',
        'longitude',
        'notes',
        'icon',
        'type',
        'completed',
        'cancelled',
        'participant_id'
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'completed' => 'boolean',
        'cancelled' => 'boolean',
    ];

    // İlişkiler (Örnek)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
