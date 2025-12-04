<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScreenService;

class ScreenController extends Controller
{
    public function __construct(private ScreenService $service) {}

    public function index()
    {
        return view('screens.index');
    }

    public function list()
    {
        return response()->json([
            "data" => $this->service->getAll()
        ]);
    }

    public function get($id)
    {
        return response()->json($this->service->getById($id));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        if ($request->hasFile('hero_image_file')) {
            $path = $request->hero_image_file->store('screens', 'public');
            $data['content']['hero_image']['url'] = asset('storage/' . $path);
        }

        $screen = $this->service->update($id, $data);

        return response()->json([
            "message" => "Updated successfully",
            "data" => $screen
        ]);
    }
}
