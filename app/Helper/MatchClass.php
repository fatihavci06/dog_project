<?php

namespace App\Helper;

class MatchClass
{
    public static function normalize(array $answers): array
    {
        return collect($answers)
            ->groupBy('question_id')
            ->map(
                fn($items) =>
                $items
                    ->sortBy('order_index')
                    ->pluck('option_id')
                    ->values()
                    ->toArray()
            )
            ->toArray();
    }
    public static function getMatchType(array $a, array $b): string
    {
        $perfect = true;
        $strongCount = 0;
        $goodCount = 0;

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
            return __('app.pawfect');
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
            return __('app.strong_match');
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
            return __('app.good_match');
        }


        /**
         * 🤔 No Match
         */
        return __('app.no_match');
    }
}
