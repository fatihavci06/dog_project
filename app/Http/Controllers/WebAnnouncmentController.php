<?php
namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\Role;
use App\Services\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Added for logging errors
use Exception; // Added to catch general exceptions

class WebAnnouncmentController extends Controller
{
    protected AnnouncementService $announcementService;

    public function __construct(AnnouncementService $announcementService)
    {
        $this->announcementService = $announcementService;
    }

    public function index(Request $request)
    {
        $announcements = $this->announcementService->list($request->only('search', 'role_id'));
        $roles = Role::all();

        return view('announcements.index', compact('announcements', 'roles'));
    }

    //-----------------------------------------------------------------------------------

    public function store(AnnouncementRequest $request)
    {
        try {
            $this->announcementService->create($request->validated());
            return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
        } catch (Exception $e) {
            // Log the detailed error
            Log::error('Announcement creation error: ' . $e->getMessage(), ['exception' => $e]);
            // Return an error message to the user
            return redirect()->back()->withInput()->with('error', 'Failed to create the announcement. Please try again.');
        }
    }

    //-----------------------------------------------------------------------------------

    public function update(AnnouncementRequest $request, Announcement $announcement)
    {
        try {
            $this->announcementService->update($announcement, $request->validated());
            return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
        } catch (Exception $e) {
            // Log the detailed error
            Log::error('Announcement update error: ' . $e->getMessage(), ['exception' => $e]);
            // Return an error message to the user
            return redirect()->back()->withInput()->with('error', 'Failed to update the announcement. Please try again.');
        }
    }

    //-----------------------------------------------------------------------------------

    public function destroy(Announcement $announcement)
    {
        try {
            // Check if the model was found via Route Model Binding before attempting deletion
            // Note: If the model is not found, Laravel usually throws a 404 before reaching here,
            // but this catches errors during the actual deletion process (e.g., DB constraints).
            $this->announcementService->delete($announcement);
            return redirect()->back()->with('success', 'Announcement deleted successfully.');
        } catch (Exception $e) {
            // Log the detailed error
            Log::error('Announcement deletion error: ' . $e->getMessage(), ['exception' => $e]);
            // Return an error message to the user
            return redirect()->back()->with('error', 'Failed to delete the announcement. Please try again.');
        }
    }
}
