<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PupProfileAnswer extends Model
{
    protected $fillable = [
        'pup_profile_id',
        'question_id',
        'option_id',
        'order_index',
    ];

    public function pupProfile()
    {
        return $this->belongsTo(PupProfile::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
    public function pup()
    {
        return $this->belongsTo(PupProfile::class, 'pup_profile_id');
    }
}
