<?php

namespace App\Services;

use App\Models\MobileAppInformationStepBeyStepInfo;

class MobileAppStepInfoService
{
    public function all()
    {
        return MobileAppInformationStepBeyStepInfo::with('translations.language')
            ->orderBy('step_number')
            ->get();
    }

    public function update(int $id, array $data): MobileAppInformationStepBeyStepInfo
    {
        $step = MobileAppInformationStepBeyStepInfo::findOrFail($id);

        // step_number sabit → ASLA değiştirme!

        // Resim güncelleme
        if (isset($data['image_path'])) {
            $step->image_path = $data['image_path'];
            $step->save();
        }

        // Çeviriler
        foreach ($data['title'] as $locale => $value) {
            $step->setTranslation('title', $locale, $value);
        }

        foreach ($data['description'] as $locale => $value) {
            $step->setTranslation('description', $locale, $value);
        }

        return $step;
    }
}
