<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\MobileAppStepInfoService;
use Illuminate\Http\Request;

class MobileAppStepInfoController extends Controller
{
    protected MobileAppStepInfoService $service;

    public function __construct(MobileAppStepInfoService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $languages = Language::where('is_active', 1)->get();
        $items = $this->service->all();

        return view('mobile_app_informations.stepbystep', compact('languages', 'items'));
    }

    public function update(Request $request, int $id)
    {
        $languages = Language::where('is_active', 1)->pluck('code')->toArray();

        $rules = [
            'step_number' => 'required|integer',
            'image_path' => 'nullable|image',
        ];

        foreach ($languages as $locale) {
            $rules["title.$locale"] = 'required|string';
            $rules["description.$locale"] = 'required|string';
        }

        $validated = $request->validate($rules);

        $data = $validated;

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('mobile-steps', 'public');
        }

        $this->service->update($id, $data);

        return back()->with('success', 'Step updated successfully.');
    }
}
