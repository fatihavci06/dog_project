<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'event_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
     protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
