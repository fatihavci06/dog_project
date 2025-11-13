<?php

namespace App\Http\Controllers;


use App\Models\Language;

use App\Services\PageInfoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MobilAppPageInfoController extends Controller
{
    protected $service;

    public function __construct(PageInfoService $service)
    {
        $this->service = $service;
    }

    public function pageInfo()
    {
        $items = $this->service->all();
        $languages = Language::where('is_active', 1)->get();

        return view('mobile_app_informations.pageinfo', compact('items', 'languages'));
    }


    public function update(Request $request, $id)
    {
        Log::info(22312);
        $this->validateData($request, update: true);
         Log::info(22312);
        $data = $request->all();

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('page-info', 'public');
        }

        $this->service->update($id, $data);

        return back()->with('success', 'Page successfully updated.');
    }

    private function validateData(Request $request, $update = false)
    {
        // Aktif dilleri alıyoruz (ör: ['en', 'tr'])
        $languages = \App\Models\Language::where('is_active', 1)
            ->pluck('code')
            ->toArray();

        $rules = [];

        // page_name sadece CREATE sırasında zorunlu,
        // UPDATE'de değişmemesi gerektiği için zorunlu değil
        if ($update === false) {

            $rules['page_name'] = 'required|string|max:255';
        }

        // Çok dilli title + description zorunlu
        foreach ($languages as $locale) {
            $rules["title.$locale"] = 'required|string';
            $rules["description.$locale"] = 'required|string';
        }

        // image -> create'de required, update'de optional
        if (!$update) {
            $rules['image_path'] = 'required|image';
        } else {
            $rules['image_path'] = 'nullable|image';
        }

        $request->validate($rules);
    }
}
