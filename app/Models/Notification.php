<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['title', 'message', 'url'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'notification_user')
                    ->withPivot(['is_read', 'sent_at'])
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'notification_role')
                    ->withTimestamps();
    }
}
