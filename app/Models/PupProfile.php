<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PupProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'sex',
        'breed_id',
        'age_range_id',
        'travel_radius_id',
        'lat',
        'long',
        'city',
        'district',
        'biography'
    ];

    /* ------------------ RELATIONS ------------------ */

    public function images()
    {
        return $this->hasMany(PupProfileImage::class);
    }

    public function answers()
    {
        return $this->hasMany(PupProfileAnswer::class);
    }

    public function breed()
    {
        return $this->belongsTo(Bread::class);
    }

    public function ageRange()
    {
        return $this->belongsTo(AgeRange::class);
    }

    public function travelRadius()
    {
        return $this->belongsTo(TravelRadius::class);
    }

    /* ------------------ NEW PIVOT RELATIONS ------------------ */

    public function lookingFor()
    {
        return $this->belongsToMany(LookingFor::class, 'pup_profile_looking_for');
    }

    public function vibe()
    {
        return $this->belongsToMany(Vibe::class, 'pup_profile_vibe');
    }

    public function healthInfo()
    {
        return $this->belongsToMany(HealthInfo::class, 'pup_profile_health_info');
    }

    public function availabilityForMeetup()
    {
        return $this->belongsToMany(AvailabilityForMeetup::class, 'pup_profile_availability');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function friends()
    {
        return $this->belongsToMany(
            PupProfile::class,
            'friendships',
            'sender_id',
            'receiver_id'
        )->wherePivot('status', 'accepted');
    }
}
