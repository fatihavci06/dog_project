<?php

namespace App\Services;

use App\Models\UserAnswer;
use App\Models\UserDog;
use Illuminate\Support\Facades\DB;

class DogService
{
    // Soru ve seçenek yönetimi için gerekli metotlar buraya eklenecek
    public function getList(array $data)
    {
        // Tüm aktif soruları ve her sorunun aktif seçeneklerini getir
        return UserDog::where('user_id',$data['user_id'])->get();
    }
}
