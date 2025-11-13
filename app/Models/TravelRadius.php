<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class TravelRadius extends Model
{
    protected $table='travel_radius';
   use HasTranslations;

    protected $fillable = [];
}
