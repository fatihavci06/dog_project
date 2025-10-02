<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['question_id', 'option_text', 'order_index', 'is_active',];
    /** * Seçenek bir soruya aittir */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    /** * Bir seçeneğin kullanıcı cevapları */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
