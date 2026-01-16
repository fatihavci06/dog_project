<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class PlanService
{
    /**
     * Tüm planları listele
     */
    public function getAllPlans(int $userId)
    {
        // Sadece giriş yapan kullanıcının planlarını getir
        return Plan::where('user_id', $userId)->orderBy('start_date', 'asc')->get();
    }

    /**
     * Yeni plan oluştur
     */
    public function createPlan(array $data)
    {
        // 1. Type Belirleme Mantığı
        $data['type'] = $this->determineType($data['start_date'], $data['end_date']);

        // 2. JSON key mapleme (lang -> latitude)
        $data = $this->mapCoordinates($data);

        // 3. User ID ekleme
        $data['user_id'] = $data['user_id'];

        return Plan::create($data);
    }

    /**
     * Planı güncelle
     */
    public function updatePlan(Plan $plan, array $data)
    {
        // Tarihler değişmişse Type tekrar hesaplanmalı
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = $data['start_date'] ?? $plan->start_date;
            $endDate = $data['end_date'] ?? $plan->end_date;
            $data['type'] = $this->determineType($startDate, $endDate);
        }

        // Koordinat mapleme
        $data = $this->mapCoordinates($data);

        $plan->update($data);
        return $plan;
    }

    /**
     * Plan sil
     */
    public function deletePlan(Plan $plan)
    {
        return $plan->delete();
    }

    /**
     * Yardımcı Metot: Type Hesaplama
     */
    private function determineType($startDate, $endDate)
    {
        // Tarihleri karşılaştır (Carbon instances veya string)
        return $startDate === $endDate ? 'single' : 'multi-day';
    }

    /**
     * Yardımcı Metot: Koordinat İsimlendirme
     */
    private function mapCoordinates(array $data)
    {
        if (isset($data['lang'])) {
            $data['latitude'] = $data['lang'];
            unset($data['lang']);
        }
        if (isset($data['long'])) {
            $data['longitude'] = $data['long'];
            unset($data['long']); // Modelde long yoksa unset etmesen de olur ama temizlik iyidir
        }
        return $data;
    }
}
