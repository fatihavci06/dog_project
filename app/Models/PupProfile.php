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
        'looking_for_id',
        'vibe_id',
        'health_info_id',
        'travel_radius_id',
        'availability_for_meetup_id',
        'lat',
        'long',
        'city',
        'district',
        'biography'
    ];

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

public function lookingFor()
{
    return $this->belongsTo(LookingFor::class);
}

public function vibe()
{
    return $this->belongsTo(Vibe::class);
}

public function healthInfo()
{
    return $this->belongsTo(HealthInfo::class);
}

public function travelRadius()
{
    return $this->belongsTo(TravelRadius::class);
}

public function availabilityForMeetup()
{
    return $this->belongsTo(AvailabilityForMeetup::class);
}

}
