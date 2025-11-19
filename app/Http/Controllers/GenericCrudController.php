<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenericMultilangService;
use App\Models\Language;
use Illuminate\Support\Facades\Storage;

class GenericCrudController extends Controller
{
    public function index(Request $request, $model)
    {
        $modelClass = "App\\Models\\" . $model;

        if (!class_exists($modelClass)) {
            abort(404, 'Model not found');
        }

        $languages = Language::where('is_active', 1)->get();
        $search = $request->get('search');

        $items = $modelClass::query()
            ->when($search, function ($q) use ($search) {
                $q->whereHas('translations', function ($t) use ($search) {
                    $t->where('key', 'name')
                        ->where('value', 'like', "%$search%");
                });
            })
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('generic-crud.index', compact('items', 'languages', 'model', 'search'));
    }


    public function store(Request $request, $model)
    {
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string',
            'icon' => 'nullable|mimes:png,svg'
        ]);

        $service = new GenericMultilangService("App\\Models\\" . $model);
        $created = $service->create($request->name);

        // --------------------------------------------------
        // 🔥 Vibe modelinde icon yükleme aktif edilsin
        // --------------------------------------------------
        if ($model === 'Vibe' && $request->hasFile('icon')) {
            $path = $request->file('icon')->store('icons', 'public');
            $created->icon_path = $path;
            $created->save();
        }

        return redirect()->back()->with('success', 'Successfully added.');
    }


    public function update(Request $request, $model, $id)
    {
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string',
            'icon' => 'nullable|mimes:png,svg',
            'remove_icon' => 'nullable|boolean',
        ]);

        $service = new GenericMultilangService("App\\Models\\" . $model);
        $updated = $service->update($id, $request->name);

        // --------------------------------------------------
        // 🔥 Sadece Vibe için icon upload
        // --------------------------------------------------
        if ($model === 'Vibe') {

            $vibe = ("App\\Models\\Vibe")::find($id);

            // Icon removal
            if ($request->boolean('remove_icon') && $vibe->icon_path) {
                Storage::disk('public')->delete($vibe->icon_path);
                $vibe->icon_path = null;
            }

            // New file upload
            if ($request->hasFile('icon')) {
                // eski dosyayı sil
                if ($vibe->icon_path) {
                    Storage::disk('public')->delete($vibe->icon_path);
                }

                $path = $request->file('icon')->store('icons', 'public');
                $vibe->icon_path = $path;
            }

            $vibe->save();
        }

        return redirect()->back()->with('success', 'Updated successfully.');
    }


    public function destroy($model, $id)
    {
        $service = new GenericMultilangService("App\\Models\\" . $model);
        $deleted = $service->delete($id);

        // 🔥 Icon dosyasını da silelim (Vibe ise)
        if ($model === 'Vibe') {
            $vibe = ("App\\Models\\Vibe")::find($id);

            if ($vibe && $vibe->icon_path) {
                Storage::disk('public')->delete($vibe->icon_path);
            }
        }

        return redirect()->back()->with('success', 'Deleted.');
    }
}
