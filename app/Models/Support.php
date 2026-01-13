<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    protected $fillable = [
        'language_code', 'title', 'description', 'email',
        'phone', 'address', 'website_url',
        'instagram_url', 'tiktok_url', 'x_url'
    ];
}
