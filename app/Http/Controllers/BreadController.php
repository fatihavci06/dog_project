<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BreadService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class BreadController extends Controller
{
    protected $breadService;

    public function __construct(BreadService $breadService)
    {
        $this->breadService = $breadService;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $breads = $this->breadService->getPaginated($search);

        return view('breads.index', compact('breads', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->breadService->create($request->only('name'));

            return response()->json(['success' => true, 'message' => 'Breed successfully added.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to add breed: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->breadService->update($id, $request->only('name'));

            return response()->json(['success' => true, 'message' => 'Breed successfully updated.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Breed not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update breed: ' . $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        try {
            $this->breadService->delete($id);

            return response()->json(['success' => true, 'message' => 'Breed successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Breed not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete breed: ' . $e->getMessage()], 500);
        }
    }
}
