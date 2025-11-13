<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreadTranslation extends Model
{
    protected $fillable = ['bread_id', 'language_id', 'name'];

    public function bread()
    {
        return $this->belongsTo(Bread::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
