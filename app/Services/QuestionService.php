<?php

namespace App\Services;

use App\Models\UserAnswer;
use App\Models\UserDog;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    // Soru ve seçenek yönetimi için gerekli metotlar buraya eklenecek
    public function  getAllQuestionsWithOptions()
    {

        // Tüm aktif soruları ve her sorunun aktif seçeneklerini getir
        return \App\Models\Question::with(['options' => function ($query) {
            $query->where('is_active', true)->orderBy('order_index');
        }])->where('is_active', true)->get();
    }
    public function userQuestionAnswerUpdateOrCreate(array $data)
    {
        $answers = $data['answers'];
        $userId = $data['user_id'];
        $roleId = $data['role_id'];
        $dogData = $data['user_dogs'][0] ?? null; // köpek bilgisi varsa
        // Question IDs array formatında


        DB::transaction(function () use ($userId, $answers, $roleId, $dogData) {

            foreach ($answers as $answer) {
                $questionId = $answer['question_id'];
                $options = $answer['options'];

                // Önce eski cevapları sil (question bazlı)
                UserAnswer::where('user_id', $userId)
                    ->where('role_id', $roleId)
                    ->where('question_id', $questionId)
                    ->delete();

                // Yeni cevapları insert et
                foreach ($options as $option) {
                    UserAnswer::create([
                        'role_id' => $roleId,
                        'user_id' => $userId,
                        'question_id' => $questionId,
                        'option_id' => $option['option_id'],
                        'rank' => $option['rank'],
                    ]);
                }
            }
            if ($dogData) {
                $updateData = [
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

                UserDog::updateOrCreate(
                    [
                        'user_id' => $userId,

                    ],
                    $updateData
                );
            }
        });

        return ['message' => 'Answers saved successfully.'];
    }
}
