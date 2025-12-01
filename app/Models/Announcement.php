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
      public function getCreatedAtAttribute($value)
    {
        return $value ? date('d-m-Y H:i:s', strtotime($value)) : null;
    }

    /** 🔥 updated_at format */
    public function getUpdatedAtAttribute($value)
    {
        return $value ? date('d-m-Y H:i:s', strtotime($value)) : null;
    }

}
