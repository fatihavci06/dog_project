<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{

    protected $table = 'notification_user';

    protected $fillable = ['notification_id', 'user_id', 'is_read', 'sent_at'];
}
