<?php

namespace App\Traits;

use App\Models\Language;

trait HasPageInfoTranslations
{
    public function translations()
    {
        return $this->morphMany(\App\Models\Translation::class, 'translatable');
    }

    public function translate($key, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->translations()
            ->where('key', $key)
            ->whereHas('language', fn($q) => $q->where('code', $locale))
            ->first()
            ?->value;
    }

    public function setTranslation($key, $locale, $value)
    {
        $language = Language::where('code', $locale)->firstOrFail();

        return $this->translations()->updateOrCreate(
            [
                'key'        => $key,
                'language_id'=> $language->id
            ],
            [
                'value'      => $value
            ]
        );
    }

    public function getTitleAttribute()
    {
        return $this->translate('title');
    }

    public function getDescriptionAttribute()
    {
        return $this->translate('description');
    }
}
