<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileAppStepByStepUpdateRequest;
use App\Models\MobileAppInformationStepBeyStepInfo;
use Illuminate\Http\Request;

class MobileAppInformationsController extends Controller
{
    public function index()
    {
        $data = MobileAppInformationStepBeyStepInfo::all();
        return view('mobile_app_informations.stepbystep', compact('data'));
    }
    public function update(MobileAppStepByStepUpdateRequest $request)
    {
        $id = $request->input('id');
        $step = MobileAppInformationStepBeyStepInfo::find($id);
        $step->step_description = $request->input('description');
        $step->save();
        return response()->json([
            'success' => true,
            'message' => 'Information updated successfully.'
        ]);
    }
}
