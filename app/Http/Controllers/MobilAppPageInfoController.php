<?php

namespace App\Http\Controllers;

use App\Http\Requests\MobileAppPageInfoUpdateRequest;
use App\Models\pageInfo;
use App\Services\MobileAppInfoService;
use Illuminate\Http\Request;

class MobilAppPageInfoController extends Controller
{

    protected $mobileAppInfoService;

    // Servis sınıfını constructor injection ile enjekte et
    public function __construct(MobileAppInfoService $mobileAppInfoService)
    {
        $this->mobileAppInfoService = $mobileAppInfoService;
    }
    public function pageInfo()
    {
        $pageInfo = pageInfo::select(['id', 'page_name', 'title', 'description', 'image_path'])->get();
        return view('mobile_app_informations.pageinfo', compact('pageInfo'));
    }
    public function pageInfoUpdate(MobileAppPageInfoUpdateRequest $request)
    {
        // 1. Gelen Verileri Al
        $id = $request->input('id');
        $title = $request->input('title');
        $description = $request->input('description');
        $imageFile = $request->file('image_file');

        try {
            // 2. İşlemi Servis Katmanına Devret
            $result = $this->mobileAppInfoService->updatePageInfo($id, $title, $description, $imageFile);

            $page = $result['page'];
            $newImagePath = $result['new_image_path'];

            // 3. Başarılı Yanıtı Döndür
            return response()->json([
                'success' => true,
                'message' => "Successfully. ",
                'new_image_path' => $newImagePath
            ]);
        } catch (\Exception $e) {
            // Hata yakalanırsa (örneğin kayıt bulunamazsa)
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400); // 400 Bad Request veya uygun bir hata kodu
        }
    }
}
