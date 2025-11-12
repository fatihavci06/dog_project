<?php

namespace App\Services;

use App\Models\PageInfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MobileAppInfoService
{

    public function updatePageInfo(int $id, string $title, string $description, ?UploadedFile $imageFile): array
    {
        // 1. Kaydı Bul
        $page = PageInfo::find($id);

        if (!$page) {
            throw new \Exception('Sayfa kaydı bulunamadı.');
        }

        $newImagePath = null;


        if ($imageFile) {

            $uploadedPath = Storage::disk('public')->put('page_images', $imageFile);
            $newImagePath = $uploadedPath;

            // Eğer eski bir resim varsa, depolamadan sil
            if ($page->image_path && Storage::disk('public')->exists($page->image_path)) {
                Storage::disk('public')->delete($page->image_path);
            }
        }


        $page->title = $title;
        $page->description = $description;

        if ($newImagePath) {
            $page->image_path = $newImagePath;
        }

        $page->save();

        return [
            'page' => $page,
            'new_image_path' => $newImagePath,
        ];
    }
}
