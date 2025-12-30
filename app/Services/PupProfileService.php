<?php

namespace App\Services;

use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use App\Models\PupProfileImage;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PupProfileService
{
    public function updateSurvey($pupId, $answers)
    {
        $pup = PupProfile::findOrFail($pupId);

        // 1) Mevcut tüm cevapları sil — ranking sorusunda gerekli
        $pup->answers()->delete();

        // 2) Yeni gönderilen cevapları sırayla ekle
        foreach ($answers as $answerBlock) {

            $questionId = $answerBlock['question_id'];
            $selected   = $answerBlock['selected']; // option_id + order

            foreach ($selected as $item) {

                $optionId = $item['option_id'];
                $order    = $item['order'];

                // 3) Option o soruya ait mi? (güvenlik)
                $valid = DB::table('options')
                    ->where('id', $optionId)
                    ->where('question_id', $questionId)
                    ->exists();

                if (!$valid) {
                    throw new \Exception("Option {$optionId} does not belong to Question {$questionId}");
                }

                // 4) Cevabı kaydet
                $pup->answers()->create([
                    'question_id' => $questionId,
                    'option_id'   => $optionId,
                    'order_index' => $order
                ]);
            }
        }

        // 5) Güncellenmiş formatlı survey'i döndür
        return $this->formattedSurvey($pupId);
    }


    public function formattedSurvey($pupId)
    {
        $pup = PupProfile::with([
            'answers.option',
            'answers.question'
        ])->findOrFail($pupId);

        return $pup->answers
            ->groupBy('question_id')
            ->map(function ($group) {

                $q = $group->first()->question;

                return [
                    'question_id'   => $q->id,
                    'question_text' => $q->question_text,
                    'selected'      => $group
                        ->sortBy('order_index')
                        ->map(fn($ans) => [
                            'option_id'   => $ans->option->id,
                            'option_text' => $ans->option->option_text,
                            'order'       => $ans->order_index,
                        ])
                        ->values()
                ];
            })
            ->values();
    }
    public function myPups($userId)
    {
        $pups = PupProfile::with([
            'breed',
            'ageRange',
            'lookingFor',
            'vibe',
            'healthInfo',
            'travelRadius',
            'availabilityForMeetup',
            'images',
            'answers.option',
            'answers.question'
        ])
            ->where('user_id', $userId)
              ->orderByDesc('created_at')
            ->get();

        return $pups->map(function ($p) {

            return [
                'id'          => $p->id,
                'user_id'     => $p->user_id,
                'name'        => $p->name,
                'sex'         => $p->sex,

                // Tekli FK'ler
                'breed'       => $p->breed ? ['id' => $p->breed->id, 'name' => $p->breed->name] : null,
                'age_range'   => $p->ageRange ? ['id' => $p->ageRange->id, 'name' => $p->ageRange->name] : null,
                'travel_radius' => $p->travelRadius
                    ? ['id' => $p->travelRadius->id, 'name' => $p->travelRadius->name]
                    : null,

                // Çoklu pivot alanlar
                'looking_for' => $p->lookingFor->map(fn($i) => [
                    'id'   => $i->id,
                    'name' => $i->name
                ]),

                'vibe' => $p->vibe->map(fn($i) => [
                    'id'   => $i->id,
                    'name' => $i->name
                ]),

                'health_info' => $p->healthInfo->map(fn($i) => [
                    'id'   => $i->id,
                    'name' => $i->name
                ]),

                'availability_for_meetup' => $p->availabilityForMeetup->map(fn($i) => [
                    'id'   => $i->id,
                    'name' => $i->name
                ]),

                // Konum
                'lat'       => $p->lat,
                'long'      => $p->long,
                'city'      => $p->city,
                'district'  => $p->district,
                'biography' => $p->biography,

                // Resimler
                'images' => $p->images->map(fn($img) => [
                    'id'   => $img->id,
                    'path' => $img->path
                ]),
            ];
        });
    }

    public function getSurveyAnswers($pupId)
    {
        $pup = PupProfile::with([
            'answers.option',
            'answers.question'
        ])->findOrFail($pupId);

        return $pup->answers
            ->groupBy('question_id')
            ->map(function ($group) {

                $question = $group->first()->question;

                return [
                    'question_id'   => $question->id,
                    'question_text' => $question->question_text,

                    'selected' => $group
                        ->sortBy('order_index')
                        ->map(function ($ans) {
                            return [
                                'option_id'   => $ans->option->id,
                                'option_text' => $ans->option->option_text,
                                'order'       => $ans->order_index
                            ];
                        })
                        ->values()
                ];
            })
            ->values();
    }
    public function getPupProfileDetails($pupId)
    {

        $p = PupProfile::with([
            'breed',
            'ageRange',
            'lookingFor',
            'vibe',
            'healthInfo',
            'travelRadius',
            'availabilityForMeetup',
            'images',
            'answers.option',
            'answers.question'
        ])
            ->where('id', $pupId)
            ->firstOrFail();

        return [
            'id'          => $p->id,
            'user_id'     => $p->user_id,
            'name'        => $p->name,
            'sex'         => $p->sex,

            // Tekli FK'ler
            'breed'       => $p->breed
                ? ['id' => $p->breed->id, 'name' => $p->breed->name]
                : null,

            'age_range'   => $p->ageRange
                ? ['id' => $p->ageRange->id, 'name' => $p->ageRange->name]
                : null,

            'travel_radius' => $p->travelRadius
                ? ['id' => $p->travelRadius->id, 'name' => $p->travelRadius->name]
                : null,

            // Çoklu pivot alanlar
            'looking_for' => $p->lookingFor->map(fn($i) => [
                'id'   => $i->id,
                'name' => $i->name
            ]),

            'vibe' => $p->vibe->map(fn($i) => [
                'id'   => $i->id,
                'name' => $i->name
            ]),

            'health_info' => $p->healthInfo->map(fn($i) => [
                'id'   => $i->id,
                'name' => $i->name
            ]),

            'availability_for_meetup' => $p->availabilityForMeetup->map(fn($i) => [
                'id'   => $i->id,
                'name' => $i->name
            ]),

            // Konum
            'lat'       => $p->lat,
            'long'      => $p->long,
            'city'      => $p->city,
            'district'  => $p->district,
            'biography' => $p->biography,

            // Resimler
            'images' => $p->images->map(fn($img) => [
                'id'   => $img->id,
                'path' => $img->path
            ]),
        ];
    }
    public function createPupProfileForUser($user, array $data)
    {
        return DB::transaction(function () use ($user, $data) {

            /* ---------------- CREATE MAIN PROFILE ---------------- */

            $profile = PupProfile::create([
                'user_id'          => $user->id,
                'name'             => $data['name'] ?? null,
                'sex'              => $data['sex'] ?? null,
                'breed_id'         => $data['breed_id'] ?? null,
                'age_range_id'     => $data['age_range_id'] ?? null,
                'travel_radius_id' => $data['travel_radius_id'] ?? null,

                'lat'      => $data['location']['lat'] ?? null,
                'long'     => $data['location']['long'] ?? null,
                'city'     => $data['location']['city'] ?? null,
                'district' => $data['location']['district'] ?? null,

                'biography' => $data['biografy'] ?? null,
            ]);


            /* ---------------- PIVOT TABLE SYNC ---------------- */

            if (!empty($data['looking_for_id'])) {
                $profile->lookingFor()->sync($data['looking_for_id']);
            }

            if (!empty($data['vibe_id'])) {
                $profile->vibe()->sync($data['vibe_id']);
            }

            if (!empty($data['health_info_id'])) {
                $profile->healthInfo()->sync($data['health_info_id']);
            }

            if (!empty($data['availability_for_meetup_id'])) {
                $profile->availabilityForMeetup()->sync($data['availability_for_meetup_id']);
            }


            /* ---------------- SAVE IMAGES (BASE64) ---------------- */

            if (!empty($data['images'])) {
                foreach ($data['images'] as $imageBase64) {
                    $this->saveBase64Image($profile, $imageBase64);
                }
            }


            /* ---------------- SAVE SURVEY ANSWERS ---------------- */
            if (!empty($data['answers'])) {
                foreach ($data['answers'] as $answer) {

                    $questionId = $answer['question_id'];
                    $orderedOptions = $answer['ordered_option_ids'];

                    foreach ($orderedOptions as $index => $optionId) {
                        PupProfileAnswer::create([
                            'pup_profile_id' => $profile->id,
                            'question_id'    => $questionId,
                            'option_id'      => $optionId,
                            'order_index'    => $index + 1,
                        ]);
                    }
                }
            }

            return $profile;
        });
    }

    public function updatePupProfileForUser($user, $pupId, array $data)
    {
        DB::transaction(function () use ($user, $pupId, $data) {

            /* ---------------- FIND PROFILE ---------------- */
            $profile = PupProfile::where('id', $pupId)
                ->where('user_id', $user->id)
                ->first();

            if (!$profile) {
                throw new \Exception("Pup not found or unauthorized", 404);
            }

            /* ---------------- UPDATE MAIN PROFILE ---------------- */
            $profile->update([
                'name'            => $data['name'] ?? $profile->name,
                'sex'             => $data['sex'] ?? $profile->sex,
                'breed_id'        => $data['breed_id'] ?? $profile->breed_id,
                'age_range_id'    => $data['age_range_id'] ?? $profile->age_range_id,
                'travel_radius_id' => $data['travel_radius_id'] ?? $profile->travel_radius_id,

                // location
                'lat'      => $data['location']['lat'] ?? $profile->lat,
                'long'     => $data['location']['long'] ?? $profile->long,
                'city'     => $data['location']['city'] ?? $profile->city,
                'district' => $data['location']['district'] ?? $profile->district,

                // biography
                'biography' => $data['biografy'] ?? $profile->biography,
            ]);

            /* ---------------- SYNC MULTIPLE PIVOT RELATIONS ---------------- */

            if (isset($data['looking_for_id'])) {
                $profile->lookingFor()->sync($data['looking_for_id']);
            }

            if (isset($data['vibe_id'])) {
                $profile->vibe()->sync($data['vibe_id']);
            }

            if (isset($data['health_info_id'])) {
                $profile->healthInfo()->sync($data['health_info_id']);
            }

            if (isset($data['availability_for_meetup_id'])) {
                $profile->availabilityForMeetup()->sync($data['availability_for_meetup_id']);
            }


            /* ---------------- SAVE NEW IMAGES (BASE64) ---------------- */
            if (array_key_exists('images', $data)) {

                // 1️⃣ Eski fotoğrafları sil
                $profile->images()->delete();

                // 2️⃣ Yenilerini ekle
                foreach ($data['images'] as $imageBase64) {
                    $this->saveBase64Image($profile, $imageBase64);
                }
            }
        });
    }




    /* ============================================================
       IMAGE UPLOAD (BASE64)
       ============================================================ */

    protected function saveBase64Image(PupProfile $profile, string $base64)
    {
        $image = str_replace('data:image/jpeg;base64,', '', $base64);
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $filename = 'pups/' . Str::uuid() . '.jpg';

        Storage::disk('public')->put($filename, base64_decode($image));

        PupProfileImage::create([
            'pup_profile_id' => $profile->id,
            'path'           => $filename,
        ]);
    }


    /* ============================================================
       SAVE ANSWERS (ranking)
       ============================================================ */

    protected function saveAnswer(PupProfile $profile, array $answer)
    {
        $questionId = $answer['question_id'];
        $orderedOptionIds = $answer['ordered_option_ids'];

        foreach ($orderedOptionIds as $index => $optionId) {

            // Option-question eşleşmesi kontrolü (security)
            $isValid = DB::table('options')
                ->where('id', $optionId)
                ->where('question_id', $questionId)
                ->exists();

            if (!$isValid) {
                throw new \Exception("Option $optionId question $questionId ile eşleşmiyor.");
            }

            PupProfileAnswer::create([
                'pup_profile_id' => $profile->id,
                'question_id'    => $questionId,
                'option_id'      => $optionId,
                'order_index'    => $index + 1, // 1,2,3,4,...
            ]);
        }
    }
    public function syncAnswers(int $pupProfileId, array $incomingAnswers)
    {
        // Tüm eski cevapları çek (question_id -> answer listesi)
        $existing = PupProfileAnswer::where('pup_profile_id', $pupProfileId)
            ->get()
            ->groupBy('question_id');

        foreach ($incomingAnswers as $answer) {

            $questionId = $answer['question_id'];
            $newOptionIds = $answer['ordered_option_ids'];

            $oldAnswers = $existing[$questionId] ?? collect([]);

            // Eğer eski ve yeni tamamen aynıysa -> hiçbir şey yapma
            if ($this->isSameAnswerSet($oldAnswers, $newOptionIds)) {
                unset($existing[$questionId]);
                continue;
            }

            // Farklıysa eskiyi sil → yeniyi ekle
            PupProfileAnswer::where('pup_profile_id', $pupProfileId)
                ->where('question_id', $questionId)
                ->delete();

            $order = 1;
            foreach ($newOptionIds as $optId) {
                PupProfileAnswer::create([
                    'pup_profile_id' => $pupProfileId,
                    'question_id' => $questionId,
                    'option_id' => $optId,
                    'order_index' => $order++,
                ]);
            }

            unset($existing[$questionId]);
        }

        // Artık gönderilmeyen eski sorular varsa → sil
        foreach ($existing as $questionId => $unused) {
            PupProfileAnswer::where('pup_profile_id', $pupProfileId)
                ->where('question_id', $questionId)
                ->delete();
        }
    }

    private function isSameAnswerSet($oldAnswers, $newIds)
    {
        if ($oldAnswers->count() !== count($newIds)) {
            return false;
        }

        foreach ($oldAnswers as $index => $ans) {
            if ((int)$ans->option_id !== (int)$newIds[$index]) {
                return false;
            }
        }

        return true;
    }
    public function deletePupProfile($pupId, $userId)
    {
        $pup = PupProfile::with('images')->find($pupId);
        if (!$pup || $pup->user_id !== $userId) {
            throw new \Exception('Not Found', 404);
        }

        DB::transaction(function () use ($pup) {

            // 2) Image dosyalarını storage'dan sil
            foreach ($pup->images as $img) {
                if ($img->path) {
                    // path = storage/pups/xxxx.jpg
                    $relativePath = str_replace(url('storage') . '/', '', $img->path);
                    Storage::disk('public')->delete($relativePath);
                }
            }

            // 3) Image kayıtlarını veritabanından sil
            $pup->images()->delete();

            // 4) Survey cevaplarını sil
            $pup->answers()->delete();

            // 5) Pup Profile'ı sil
            $pup->delete();
        });
    }
}
