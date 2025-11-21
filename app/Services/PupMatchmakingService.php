<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\PupProfile;
use App\Models\PupProfileAnswer;
use Exception;

class PupMatchmakingService
{
    /**
     * PupProfile'ın tüm cevaplarını getirir.
     * Format:
     * [
     *   1 => [4,1,7,2,6],   // question_id => ordered option ids
     *   2 => [12,11,10,13],
     *   3 => [...],
     *   4 => [...],
     *   5 => [...]
     * ]
     */
    public function getPupAnswers(int $pupProfileId): array
    {
        $answers = PupProfileAnswer::where('pup_profile_id', $pupProfileId)
            ->orderBy('question_id')
            ->orderBy('order_index')
            ->get()
            ->groupBy('question_id');

        $formatted = [];

        foreach ($answers as $qId => $rows) {
            $formatted[$qId] = $rows->pluck('option_id')->toArray();
        }

        return $formatted;
    }


    /**
     * Match tipini hesaplar – güncel kurallar:
     *
     * 💘 Perfect Match
     *   - 5 sorunun tamamında ilk 2 seçenek eşleşmeli (sırası önemsiz)
     *
     * 💪 Strong Match
     *   - İlk soru ilk 2 seçenek eşleşmeli (FIX)
     *   - Toplam 5 sorudan en az 3 tanesinde ilk 2 eşleşmeli
     *
     * 🙂 Good Match
     *   - İlk soru ilk 3 seçenek eşleşmeli (FIX)
     *   - Toplam 5 sorudan en az 3 tanesinde ilk 3 eşleşmeli
     *
     * 🤔 No Match
     *   - Diğer tüm durumlar
     */
    public function getMatchType(array $a, array $b): string
    {
        $perfect = true;
        $strongCount = 0;
        $goodCount   = 0;

        foreach ($a as $qId => $aAns) {

            if (!isset($b[$qId])) {
                $perfect = false;
                continue;
            }

            $bAns = $b[$qId];

            $a2 = collect($aAns)->take(2)->sort()->values();
            $b2 = collect($bAns)->take(2)->sort()->values();

            $a3 = collect($aAns)->take(3)->sort()->values();
            $b3 = collect($bAns)->take(3)->sort()->values();

            // perfect → İlk 2 seçenek tüm sorularda eşleşmeli
            if ($a2->toJson() !== $b2->toJson()) {
                $perfect = false;
            }

            // Strong → ilk 2 eşleşmesi
            if ($a2->toJson() === $b2->toJson()) {
                $strongCount++;
            }

            // Good → ilk 3 eşleşmesi
            if ($a3->toJson() === $b3->toJson()) {
                $goodCount++;
            }
        }

        /**
         * 💘 Perfect Match
         */
        if ($perfect) {
            return 'Perfect';
        }


        /**
         * 💪 Strong Match
         *
         * 1. Soru FIX → İlk 2 seçenek eşleşmeli
         * + StrongCount >= 3
         */
        $first2_Q1_A = collect($a[1])->take(2)->sort()->values()->toJson();
        $first2_Q1_B = collect($b[1] ?? [])->take(2)->sort()->values()->toJson();

        $q1StrongFix = ($first2_Q1_A === $first2_Q1_B);

        if ($q1StrongFix && $strongCount >= 3) {
            return 'Strong';
        }


        /**
         * 🙂 Good Match
         *
         * 1. Soru FIX → İlk 3 seçenek eşleşmeli
         * + GoodCount >= 3
         */
        $first3_Q1_A = collect($a[1])->take(3)->sort()->values()->toJson();
        $first3_Q1_B = collect($b[1] ?? [])->take(3)->sort()->values()->toJson();

        $q1GoodFix = ($first3_Q1_A === $first3_Q1_B);

        if ($q1GoodFix && $goodCount >= 3) {
            return 'Good';
        }


        /**
         * 🤔 No Match
         */
        return 'No Match';
    }


    /**
     * Match tipini puana dönüştürür (sıralama için)
     */
    public function matchScore(string $matchType): int
    {
        return match ($matchType) {
            'Perfect' => 4,
            'Strong'  => 3,
            'Good'    => 2,
            default   => 1,
        };
    }


    /**
     * Tüm PupProfile’lar ile eşleşme listesi döner.
     * Kendi user'a ait PupProfile'lar HARİÇ!
     */
    public function getMatchesPaginated(
        int $pupProfileId,
        int $authUserId,
        int $page = 1,
        int $perPage = 10
    ): array {

        // 1) Bu profile gerçekten giriş yapan kullanıcıya mı ait?
        if (!PupProfile::where('id', $pupProfileId)->where('user_id', $authUserId)->exists()) {
            throw new Exception('Not found', 404);
        }

        // 2) Kullanıcının arkadaş ID’lerini çek
        $friendIds = Friendship::where(function ($q) use ($authUserId) {
            $q->where('sender_id', $authUserId)
                ->where('status', 'accepted');
        })
            ->orWhere(function ($q) use ($authUserId) {
                $q->where('receiver_id', $authUserId)
                    ->where('status', 'accepted');
            })
            ->get()
            ->map(fn($f) => $f->sender_id == $authUserId ? $f->receiver_id : $f->sender_id)
            ->toArray();

        // ❗ arkadaşların PupProfile ID’lerini bul
        $friendProfileIds = PupProfile::whereIn('user_id', $friendIds)->pluck('id')->toArray();

        // 3) Ana profilin cevaplarını al
        $mainAnswers = $this->getPupAnswers($pupProfileId);

        // 4) Diğer profilleri getir → kendi profili + kendi user_id + arkadaş profilleri hariç
        $otherProfiles = PupProfile::with(['images', 'vibe'])
            ->where('id', '!=', $pupProfileId)
            ->where('user_id', '!=', $authUserId)
            ->whereNotIn('id', $friendProfileIds) // 🔥 arkadaşlar çıkartıldı
            ->get();

        $result = [];

        // 5) Eşleşmeleri hesapla
        foreach ($otherProfiles as $profile) {

            $otherAnswers = $this->getPupAnswers($profile->id);

            $matchType = $this->getMatchType($mainAnswers, $otherAnswers);
            $score     = $this->matchScore($matchType);

            $result[] = [
                'profile_id'  => $profile->id,
                'name'        => $profile->name,
                'photo'       => $profile->images[0]->path ?? null,
                'user_id'     => $profile->user_id,
                'biography'   => $profile->biography,
                'vibe' => $profile->vibe->map(fn($v) => [
                    'id'   => $v->id,
                    'name' => $v->name,
                ]),
                'match_type'  => $matchType,
                'match_score' => $score,
            ];
        }

        // 6) Score'a göre sırala
        $sorted = collect($result)->sortByDesc('match_score')->values();

        // 7) Pagination
        $total     = $sorted->count();
        $lastPage  = (int) ceil($total / $perPage);
        $offset    = ($page - 1) * $perPage;

        $paged = $sorted->slice($offset, $perPage)->values()->toArray();

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $paged,
        ];
    }
}
