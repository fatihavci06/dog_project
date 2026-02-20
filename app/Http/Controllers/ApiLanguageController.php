<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ApiLanguageController extends ApiController
{
    public function changeLanguageStatus(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,es,fr,de,it,pt,ru,zh,ja,ko,tr'
        ]);

        // Kullanıcının dil tercihini güncelle
        $user = User::find($request->user_id);
        $user->preferred_language = $request->language;
        $user->save();

        App::setLocale($request->language);

    }
}
