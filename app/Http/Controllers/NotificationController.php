<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    // Gönderim formu
    public function create()
    {
        $users = User::all();
        $roles = Role::all();
        return view('notifications.create', compact('users', 'roles'));
    }

    // Gönderim işlemi
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
            'user_ids' => 'nullable|array',
            'role_ids' => 'nullable|array'
        ]);

        $notification = $this->service->sendNotification($data);

        return redirect()->back()->with('success', 'Notification success sended.');
    }

    // Gönderilen bildirimlerin listesi
    public function index()
    {
        $notifications = Notification::with(['users'])->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }
    
}
