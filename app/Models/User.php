<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
        'one_signal_player_id',
        'location_city',
        'location_district',
        'biography',
        'photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'photo', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // JWT custom claims
    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email,
        ];
    }
    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
    public function userDogs()
    {
        return $this->hasMany(UserDog::class);
    }

    protected $appends = ['photo_url']; // response'a otomatik eklensin

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? url('storage/' . $this->photo) : null;
    }
    public function testUserRoles()
    {
        return $this->hasMany(TestUserRole::class);
    }
}
