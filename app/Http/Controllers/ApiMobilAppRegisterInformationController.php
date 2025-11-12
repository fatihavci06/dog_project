<?php

namespace App\Http\Controllers;

use App\Models\AgeRange;
use App\Models\AvailabilityForMeetup;
use App\Models\Bread;
use App\Models\HealthInfo;
use App\Models\LookingFor;
use App\Models\MobileAppInformationStepBeyStepInfo;
use App\Models\pageInfo;
use App\Models\TravelRadius;
use App\Models\Vibe;
use App\Services\BreadService;
use Illuminate\Http\Request;

class ApiMobilAppRegisterInformationController extends ApiController
{

    public function stepByStepInfo()
    {
        return MobileAppInformationStepBeyStepInfo::select(['step_number', 'step_description'])->get();
    }
    public function pageInfo()
    {
        return  pageInfo::select(['page_name', 'title', 'description', 'image_path'])->get();
    }
    public function basicInfo()
    {

        $basicInfo = [
            'breed' => Bread::all(),
            'age_range'=>AgeRange::all(),
            'looking_for'=>LookingFor::all(),
            'vibe'=>Vibe::all(),
            'health_info'=>HealthInfo::all(),
            'travel_radius'=>TravelRadius::all(),
            'availability_for_meetups'=>AvailabilityForMeetup::all(),
        ];
        return $basicInfo;
    }
}
