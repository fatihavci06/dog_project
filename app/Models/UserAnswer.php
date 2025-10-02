<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAnswer extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'question_id', 'option_id', 'rank', 'role_id'];
    /** * Cevap bir kullanıcıya aittir */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /** * Cevap bir soruya aittir */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    /** * Cevap bir seçeneğe aittir */
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
