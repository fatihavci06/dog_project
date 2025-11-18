<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class AgeRange extends Model
{
   use HasTranslations;

    protected $fillable = [];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
