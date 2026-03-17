<?php

namespace App\Http\Controllers;

use App\Http\Requests\PupProfileCreateRequest;
use App\Http\Requests\PupProfileUpdateRequest;
use App\Http\Requests\SurveyUpdateRequest;
use App\Http\Requests\UpdateLoverLocationRequest;
use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use App\Models\Question;
use App\Models\User;
use App\Services\PupProfileService;
use Exception;
use Illuminate\Http\Request;

class ApiPupProfileController extends ApiController
{
    private $service;

    public function __construct(PupProfileService $service)
    {
        $this->service = $service;
    }
    public function updateSurvey(SurveyUpdateRequest $request, $pupId)
    {


        return $this->service->updateSurvey($pupId, $request->answers);
    }
    public function getAnswers(Request $request, $pupId)
    {

        $control = PupProfile::where('user_id', $request->user_id)->where('id', $pupId)->exists();
        if (!$control) {
            throw new Exception('Not found.', 404);
        }
        return  $this->service->getSurveyAnswers($pupId);
    }
    public function myPups(Request $request)
    {


        return $this->service->myPups($request->user_id);
    }
    public function destroy($pupId, Request $request)
    {
        return $this->service->deletePupProfile($pupId, $request->user_id);
    }
    public function store(PupProfileCreateRequest $request)
    {
        return  $this->service->createPupProfileForUser(
            User::find($request->user_id),
            $request->validated()
        );
    }
    public function myPupShow(Request $request, $id)
    {
        $control = PupProfile::where('user_id', $request->user_id)->where('id', $id)->exists();
        if (!$control) {
            throw new Exception('Not found.', 404);
        }
        return $this->service->getPupProfileDetails($id);
    }
    public function update(PupProfileUpdateRequest $request, $id)
    {
        return $this->service->updatePupProfileForUser(
            User::find($request->user_id),
            $id,
            $request->validated()
        );


    }

    public function updateLoverLocation(UpdateLoverLocationRequest $request)
    {
        $user = User::findOrFail($request->user_id);

        if ((int) $user->role_id !== 4) {
            throw new Exception('Forbidden', 403);
        }

        $pupProfile = PupProfile::where('user_id', $user->id)->first();
        if (!$pupProfile) {
            throw new Exception('Not found.', 404);
        }

        $location = $request->validated()['location'];

        $pupProfile->update([
            'lat'      => $location['lat'],
            'long'     => $location['long'],
            'city'     => $location['city'] ?? $pupProfile->city,
            'district' => $location['district'] ?? $pupProfile->district,
        ]);

        return [
            'message' => 'messages.success',
            'data' => [
                'pup_profile_id' => $pupProfile->id,
                'lat' => $pupProfile->lat,
                'long' => $pupProfile->long,
                'city' => $pupProfile->city,
                'district' => $pupProfile->district,
            ],
        ];
    }
}
