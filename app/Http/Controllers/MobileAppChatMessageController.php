<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\MobileAppChatMessageService;
use Illuminate\Http\Request;

class MobileAppChatMessageController extends Controller
{
    protected MobileAppChatMessageService $service;

    public function __construct(MobileAppChatMessageService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $languages = Language::where('is_active', 1)->get();
        $items = $this->service->all();

        // Blade yolunu kendi klasör yapına göre düzenleyebilirsin
        return view('mobile_app_informations.chat_messages', compact('languages', 'items'));
    }

    // Sürükle-bırak sıralama güncellemesi için
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:mobile_app_chat_messages,id',
        ]);

        $this->service->updateOrder($request->orders);

        return response()->json(['success' => true, 'message' => 'Sıralama güncellendi.']);
    }
    public function store(Request $request)
    {
        $languages = Language::where('is_active', 1)->pluck('code')->toArray();

        $rules = [
            'type' => 'required|in:question,message',
        ];

        foreach ($languages as $locale) {
            $rules["content.$locale"] = 'required|string';
        }

        $validated = $request->validate($rules);

        $this->service->store($validated);

        return back()->with('success', 'Message added successfully.');
    }

    public function update(Request $request, int $id)
    {
        $languages = Language::where('is_active', 1)->pluck('code')->toArray();

        $rules = [
            'type' => 'required|in:question,message',
        ];

        foreach ($languages as $locale) {
            $rules["content.$locale"] = 'required|string';
        }

        $validated = $request->validate($rules);

        $this->service->update($id, $validated);

        return back()->with('success', 'Message updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);

        return back()->with('success', 'Message deleted successfully.');
    }
}
