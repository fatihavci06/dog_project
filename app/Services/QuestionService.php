<?php

namespace App\Services;

use App\Models\TestUserRole;
use App\Models\User;
use App\Models\UserAnswer;
use App\Models\UserDog;
use Illuminate\Support\Facades\DB;

class QuestionService
{
    // Soru ve seçenek yönetimi için gerekli metotlar buraya eklenecek
    public function getAllQuestionsWithOptions($locale)
    {

        app()->setLocale($locale);


        return \App\Models\Question::where('is_active', true)
            ->with(['options' => function ($query) {
                $query->where('is_active', true)->orderBy('order_index');
            }])
            ->get()
            ->map(function ($question) use ($locale) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->translate('question_text', $locale),

                    'options' => $question->options->map(function ($opt) use ($locale) {
                        return [
                            'id' => $opt->id,
                            'option_text' => $opt->translate('option_text', $locale),
                            'order_index' => $opt->order_index,
                        ];
                    })
                ];
            });
    }

    public function userQuestionAnswerUpdateOrCreate(array $data)
    {

        $answers = $data['answers'];
        $userId = $data['user_id'];
        $roleId = $data['role_id'];
        $dogData = $data['user_dogs'][0] ?? null; // köpek bilgisi varsa
        // Question IDs array formatında


        DB::transaction(function () use ($userId, $answers, $roleId, $dogData, $data) {
            User::where('id', $userId)
                ->update(['role_id' => $roleId]);
            // Önce mevcut cevapları sil
            if (isset($data['test_id'])) {
                UserAnswer::where('test_id', $data['test_id'])->delete();
                TestUserRole::where('id', $data['test_id'])->update(['user_id' => $userId, 'role_id' => $roleId]);
                $testId = $data['test_id'];
            } else {
                $testId = TestUserRole::create(
                    [
                        'user_id' => $userId,
                        'role_id' => $roleId,
                    ]
                )->id;
            }

            if ($roleId == 4) { // role Köpek sahiplenmek isteyen ise
                UserAnswer::where('user_id', $userId)
                    ->delete();
            }
            foreach ($answers as $answer) {
                $questionId = $answer['question_id'];
                $options = $answer['options'];



                // Yeni cevapları insert et
                foreach ($options as $option) {
                    UserAnswer::create([
                        'test_id' => $testId,

                        'user_id' => $userId,
                        'question_id' => $questionId,
                        'option_id' => $option['option_id'],
                        'rank' => $option['rank'],
                    ]);
                }
            }
            if ($dogData && $roleId == 3) {
                $updateData = [
                    'user_id' => $userId,
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

                $dogId = UserDog::create($updateData)->id;

                TestUserRole::where('id', $testId)->update(['dog_id' => $dogId]);
            }
        });

        return ['message' => 'Answers saved successfully.'];
    }
    public function testGet(array $data)
    {

        return UserAnswer::where('user_id', $data['user_id'])->where('test_id', $data['test_id'])->get();
    }
}
