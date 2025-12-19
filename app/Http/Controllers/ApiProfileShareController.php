<?php

namespace App\Http\Controllers;

use App\Models\PupProfile;
use App\Models\User;
use App\Services\ProfileShareService;
use Illuminate\Http\Request;

class ApiProfileShareController extends ApiController
{
    /**
     * Giriş yapmış kullanıcının profil paylaşım QR kodunu getirir.
     */
    public function generate(Request $request, ProfileShareService $service)
    {
        // Servisi çağır ve user objesini gönder
        $result = $service->generateProfileQr(PupProfile::find($request->pup_profile_id));
        return ['data'=>$result];


    }
}
