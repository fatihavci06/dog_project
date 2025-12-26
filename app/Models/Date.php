<?php

namespace App\Models;

use Carbon\Carbon;
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
        'description',
        'status',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'is_flexible'  => 'boolean',
        'latitude'     => 'float',
        'longitude'    => 'float',
    ];

    public function getMeetingDateAttribute($value): ?string
    {
        return $value
            ? Carbon::parse($value)->format('d-m-y H:i')
            : null;
    }

    public function sender()
    {
        return $this->belongsTo(PupProfile::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(PupProfile::class, 'receiver_id');
    }
}
