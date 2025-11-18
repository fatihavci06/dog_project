<?php
namespace App\Traits;

use App\Models\Language;

trait HasTranslations
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
            ->first()?->value;
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

    // Dinamik accessor: $model->name
    public function getNameAttribute()
    {
        return $this->translate('name');
    }

}
