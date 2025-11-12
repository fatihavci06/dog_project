<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileAppInformationStepBeyStepInfo extends Model
{
    //
    protected $hidden = ['created_at','updated_at'];
    protected $fillable = ['step_number','title','description'];
}
