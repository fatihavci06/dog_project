<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['question_text', 'is_active',];
    /** * Bir sorunun birden fazla seçeneği vardır */ public function options()
    {
        return $this->hasMany(Option::class);
    }
    /** * Bir sorunun kullanıcı cevapları */ public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
