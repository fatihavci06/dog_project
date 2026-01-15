<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScreenService;
use App\Models\Language; // Dil modelini ekledik

class ScreenController extends Controller
{
    public function __construct(private ScreenService $service) {}

    public function index()
    {
        // Aktif dilleri çekip view'a gönderiyoruz
        $languages = Language::where('is_active', 1)->get();
        return view('screens.index', compact('languages'));
    }

    public function list()
    {
        return response()->json([
            "data" => $this->service->getAll()
        ]);
    }

    public function get($id)
    {
        return response()->json($this->service->getById($id, null));
    }

    public function update(Request $request, $id)
    {
        // 1. Formdan gelen tüm veriyi al
        $data = $request->all();

        // 2. Veritabanındaki MEVCUT veriyi çek (Veri kaybını önlemek için)
        // Service üzerinden veya direkt modelden çekebilirsin
        $existingScreen = $this->service->getById($id);
        $existingContent = $existingScreen->content;

        $languages = \App\Models\Language::where('is_active', 1)->get();

        foreach ($languages as $lang) {
            $code = $lang->code;
            $fileInputName = 'hero_image_file_' . $code;

            // --- SENARYO 1: Yeni resim yüklendiyse ---
            if ($request->hasFile($fileInputName)) {
                $path = $request->file($fileInputName)->store('screens', 'public');

                // Yeni yolu data dizisine işle
                $data['content']['translations'][$code]['hero_image']['url'] = asset('storage/' . $path);
            }
            // --- SENARYO 2: Yeni resim YOKSA, eskisini koru ---
            else {
                // Eğer formdan URL gelmediyse veya boşsa, veritabanındaki eski URL'i alıp tekrar yerine koyuyoruz.
                if (isset($existingContent['translations'][$code]['hero_image']['url'])) {
                    $data['content']['translations'][$code]['hero_image']['url'] = $existingContent['translations'][$code]['hero_image']['url'];
                }
            }
        }

        // Güncellenmiş $data dizisini servise gönder
        $screen = $this->service->update($id, $data);

        return response()->json([
            "message" => "Updated successfully",
            "data" => $screen
        ]);
    }
}
