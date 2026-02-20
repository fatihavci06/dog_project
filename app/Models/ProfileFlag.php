<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileFlag extends Model
{
    protected $fillable = ['reporter_id', 'flagged_profile_id'];

    public function reporter() {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function flaggedProfile() {
        return $this->belongsTo(PupProfile::class, 'flagged_profile_id');
    }
}
