<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Show the form for creating a new location.
     * Also fetches all existing locations to display on the map.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $locations = Location::all();
        return view('maps.index', compact('locations'));
    }

    /**
     * Store a newly created location in the database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'nullable|string',
        ]);

        Location::create($validatedData);

        return redirect()->back()->with('success', 'Location successfully saved!');
    }

    /**
     * Remove the specified location from the database.
     *
     * @param  \App\Models\Location  $location
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Location $location)
    {
        // Add authorization check here if needed (e.g., using a policy)
        // $this->authorize('delete', $location);

        $location->delete();

        return response()->json(['success' => 'Location successfully deleted.']);
    }
}
