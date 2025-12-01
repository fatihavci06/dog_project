<?php

namespace App\Http\Controllers;

use App\Http\Requests\PupProfileCreateRequest;
use App\Http\Requests\PupProfileUpdateRequest;
use App\Http\Requests\SurveyUpdateRequest;
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
    public function update(PupProfileUpdateRequest $request, $id)
    {
        return $this->service->updatePupProfileForUser(
            User::find($request->user_id),
            $id,
            $request->validated()
        );


    }
}
