<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class LookingFor extends Model
{
    use HasTranslations;

    protected $fillable = [];
    public function pupProfiles()
    {
        return $this->belongsToMany(PupProfile::class, 'pup_profile_looking_for');
    }
}
