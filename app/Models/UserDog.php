<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserDog extends Model
{
    //
    use SoftDeletes;
    protected $guarded = [];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $hidden = ['photo', 'created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['photo_url']; // response'a otomatik eklensin

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? url('storage/' . $this->photo) : null;
    }
}
