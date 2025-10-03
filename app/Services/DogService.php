<?php

namespace App\Services;

use App\Models\TestUserRole;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserDog;
use Illuminate\Support\Facades\DB;

class DogService
{
    // Soru ve seçenek yönetimi için gerekli metotlar buraya eklenecek
    public function getList(array $data)
    {
        // Tüm aktif soruları ve her sorunun aktif seçeneklerini getir
        return UserDog::where('user_id', $data['user_id'])->get();
    }
    public function delete(array $data)
    {

        $dog = UserDog::where('id', $data['dog_id'])->where('user_id', $data['user_id'])->first();
        $testId = TestUserRole::where('dog_id', $data['dog_id'])->first();
        if ($testId) {
            UserAnswer::where('test_id', $testId->id)->delete();
            $testId->delete();
        }

        if (!$dog) {
            throw new \Exception('Not Found.');
        }
        DB::transaction(function () use ($dog) {
            $dog->delete();
        });
        return ['message' => 'Dog deleted successfully.'];
    }
    public function show(array $data)
    {
        $dog = UserDog::where('id', $data['dog_id'])->where('user_id', $data['user_id'])->first();
        if (!$dog) {
            throw new \Exception('Not Found.');
        }
        return $dog;
    }
    public function update(array $dogData)
    {

        $updateData = [
            'user_id' => $dogData['user_id'],
            'name' => $dogData['name'] ?? null,
            'gender' => $dogData['gender'] ?? null,
            'age' => $dogData['age'] ?? null,
            'biography' => $dogData['biography'] ?? null,
            'food' => $dogData['food'] ?? null,
            'health_status' => $dogData['health_status'] ?? null,
            'size' => $dogData['size'] ?? null,
        ];

        // Eğer fotoğraf dosyası geldiyse yükle
        if (isset($dogData['photo']) && $dogData['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $updateData['photo'] = $dogData['photo']->store('dogs', 'public');
        }

        return  UserDog::find($dogData['dog_id'])->update($updateData);
    }
}
