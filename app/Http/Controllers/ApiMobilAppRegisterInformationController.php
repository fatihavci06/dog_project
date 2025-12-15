<?php

namespace App\Http\Controllers;

use App\Models\AgeRange;
use App\Models\AvailabilityForMeetup;
use App\Models\Bread;
use App\Models\HealthInfo;
use App\Models\LookingFor;
use App\Models\MobileAppInformationStepBeyStepInfo;
use App\Models\PageInfo;
use App\Models\TravelRadius;
use App\Models\Vibe;
use App\Services\BreadService;
use Illuminate\Http\Request;

class ApiMobilAppRegisterInformationController extends ApiController
{

    public function stepByStepInfo($locale = 'en')
    {
        app()->setLocale($locale);

        $data = MobileAppInformationStepBeyStepInfo::with('translations.language')
            ->orderBy('step_number')
            ->get()
            ->map(function ($item) use ($locale) {
                return [
                    'step_number' => $item->step_number,
                    'title' => $item->translate('title', $locale),
                    'description' => $item->translate('description', $locale),
                    'image_path' => $item->image_path,
                ];
            });
        return [
            'data' => $data
        ];
    }
    public function pageInfo($locale = 'en')
    {
        app()->setLocale($locale); // Trait'in doğru çalışması için

        $data = PageInfo::with('translations.language')
            ->get()
            ->map(function ($item) use ($locale) {
                return [
                    'page_name'   => $item->page_name,
                    'title'       => $item->translate('title', $locale),
                    'description' => $item->translate('description', $locale),
                    'image_path'  => $item->image_path,
                ];
            });
        return [
            'data' => $data
        ];
    }

    public function basicInfo($locale = 'en')
    {

        $models = [
            'breed' => \App\Models\Bread::class,
            'age_range' => \App\Models\AgeRange::class,
            'looking_for' => \App\Models\LookingFor::class,
            'vibe' => \App\Models\Vibe::class,
            'health_info' => \App\Models\HealthInfo::class,
            'travel_radius' => \App\Models\TravelRadius::class,
            'availability_for_meetups' => \App\Models\AvailabilityForMeetup::class,
        ];

        $basicInfo = [];

        foreach ($models as $key => $modelClass) {
            $service = new \App\Services\GenericMultilangService($modelClass);
            $basicInfo[$key] = $service->listForApi($locale);
        }

        return [
            'data' => $basicInfo
        ];
    }
}
