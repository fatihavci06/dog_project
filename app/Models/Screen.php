<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screen extends Model
{
    protected $fillable = ['screen_slug', 'content'];

    protected $casts = [
        'content' => 'array'
    ];

    protected $hidden = ['created_at','updated_at'];
}
