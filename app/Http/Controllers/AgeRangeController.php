<?php

namespace App\Http\Controllers;

use App\Services\AgeRangeService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AgeRangeController extends Controller
{
     protected $service;

    public function __construct(AgeRangeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $data = $this->service->getPaginated($search);

        return view('age-range.index', compact('data', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->service->create($request->only('name'));

            return response()->json(['success' => true, 'message' => 'Age Range successfully added.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add age  range: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->service->update($id, $request->only('name'));

            return response()->json(['success' => true, 'message' => 'Age Range successfully updated.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Age Range not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update age range: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $this->service->delete($id);

            return response()->json(['success' => true, 'message' => 'Age Range successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Age Range not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete age range: ' . $e->getMessage()], 500);
        }
    }
}
