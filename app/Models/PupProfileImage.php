<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PupProfileImage extends Model
{
    protected $fillable = [
        'pup_profile_id',
        'path',
    ];

   public function pupProfile()
{
    return $this->belongsTo(PupProfile::class);
}

public function getPathAttribute($value)
{
    return $value ? url('storage/' . ltrim($value, '/')) : null;
}

}
