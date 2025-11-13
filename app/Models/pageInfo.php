<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPageInfoTranslations;

class PageInfo extends Model
{
    use HasPageInfoTranslations;

    protected $fillable = ['page_name', 'image_path'];

    protected $hidden = ['created_at', 'updated_at'];

    public function getImagePathAttribute($value)
    {
        return $value ? url('storage/' . $value) : null;
    }
}
