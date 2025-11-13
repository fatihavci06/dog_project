<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Option extends Model
{
    use \App\Traits\HasTranslations;

    protected $fillable = ['order_index', 'is_active'];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function getOptionTextAttribute()
    {
        return $this->translate('option_text');
    }
}

