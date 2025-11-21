<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Vibe extends Model
{
    use HasTranslations;

    protected $fillable = ['icon_path'];

    public function getIconPathAttribute($value)
    {
        return $value ? url('storage/' . ltrim($value, '/')) : null;
    }
    public function pupProfiles()
    {
        return $this->belongsToMany(PupProfile::class, 'pup_profile_looking_for');
    }
}
