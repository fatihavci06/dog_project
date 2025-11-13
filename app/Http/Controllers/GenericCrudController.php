<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GenericMultilangService;
use App\Models\Language;

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
            'name.*' => 'required|string'
        ]);

        $service = new GenericMultilangService("App\\Models\\" . $model);
        $service->create($request->name);

        return redirect()->back()->with('success', 'Successfully added.');
    }


    public function update(Request $request, $model, $id)
    {
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string'
        ]);

        $service = new GenericMultilangService("App\\Models\\" . $model);
        $service->update($id, $request->name);

        return redirect()->back()->with('success', 'Updated successfully.');
    }


    public function destroy($model, $id)
    {
        $service = new GenericMultilangService("App\\Models\\" . $model);
        $service->delete($id);

        return redirect()->back()->with('success', 'Deleted.');
    }
}
