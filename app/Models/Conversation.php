<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Conversation extends Model
{
    protected $fillable = ['user_one_id','user_two_id'];

    public function messages() {
        return $this->hasMany(Message::class);
    }

    public function users() {
        return $this->belongsToMany(User::class, null, 'id', 'id'); // kullanım farklı olabilir
    }

    public function otherUser($userId) {
        return $this->user_one_id == $userId ? $this->user_two_id : $this->user_one_id;
    }
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => \Carbon\Carbon::parse($value)->format('d-m-Y H:i')
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => \Carbon\Carbon::parse($value)->format('d-m-Y H:i')
        );
    }
}
