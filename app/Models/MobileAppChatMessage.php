<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPageInfoTranslations;

class MobileAppChatMessage extends Model
{
    use HasPageInfoTranslations;

    protected $fillable = ['type', 'order'];

    protected $hidden = ['created_at', 'updated_at'];
}
