<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
 protected $table = 'feedbacks';
    protected $fillable = [
        'user_id',
        'category',
        'subject',
        'message',
        'rating',
        'priority',
        'image',
        'allow_contact',
        'contact_full_name',
        'contact_email',
    ];
}
