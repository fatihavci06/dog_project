<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestUserRole extends Model
{
    protected $guarded = [];
    use SoftDeletes;
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    public function dog()
    {
        return $this->hasMany(UserDog::class, 'id', 'dog_id');
    }
}
