<?php

namespace App\Http\Controllers;

use App\Models\ProfileFlag;
use App\Models\User;
use Illuminate\Http\Request;

class WebProfileFlagController extends Controller
{
    /**
     * Display a listing of the profile flags.
     */
    public function index()
    {
        $flags = ProfileFlag::with(['reporter', 'flaggedProfile.user'])
            ->latest()
            ->paginate(20);

        return view('profile_flags.index', compact('flags'));
    }

    /**
     * Remove the specified flag from storage (Dismiss flag).
     */
    public function destroy($id)
    {
        $flag = ProfileFlag::findOrFail($id);
        $flag->delete();

        return redirect()->back()->with('success', 'Flag record has been dismissed successfully.');
    }
}
