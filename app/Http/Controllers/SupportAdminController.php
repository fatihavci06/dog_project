<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;

class SupportAdminController extends Controller
{
    public function index() { return view('support.index'); }

    public function list() {
        return response()->json(['data' => Support::all()]);
    }

    public function get($id) {
        return response()->json(Support::findOrFail($id));
    }

    public function store(Request $request, $id = null) {
        // id varsa update, yoksa create (updateOrCreate de olur)
        $support = Support::updateOrCreate(
            ['id' => $id],
            $request->all()
        );

        return response()->json(['message' => 'Success']);
    }

    public function delete($id) {
        Support::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
