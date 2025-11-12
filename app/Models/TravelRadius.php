<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TravelRadius extends Model
{
    protected $table='travel_radius';
    protected $fillable=['name'];
    protected $hidden=['created_at','updated_at'];
}
