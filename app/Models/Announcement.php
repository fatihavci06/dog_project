<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'role_id',
        'starts_at',
        'ends_at',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
