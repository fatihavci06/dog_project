<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRole extends Model
{
    //
    protected $table = 'notification_role';
    protected $fillable = ['notification_id', 'role_id'];
}
