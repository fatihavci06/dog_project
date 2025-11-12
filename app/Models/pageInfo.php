<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class pageInfo extends Model
{
    //
    protected $fillable = ['page_name', 'title', 'description', 'image_path'];
    protected $hidden = ['created_at', 'updated_at'];
    public function getImagePathAttribute($value)
    {
        return $value ? url('storage/' . $value) : null;
    }
}
