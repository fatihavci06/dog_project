<?php
namespace App\Services;

use App\Models\ProfileFlag;
use App\Mail\ProfileFlaggedMail;
use Illuminate\Support\Facades\Mail;

class FlagService
{
    public function flagProfile(int $authUserId, int $profileId)
    {
        $alreadyFlagged = ProfileFlag::where('reporter_id', $authUserId)
            ->where('flagged_profile_id', $profileId)
            ->exists();

        if ($alreadyFlagged) {
            throw new \Exception('Bu profili zaten bildirdiniz.', 422);
        }

        // 1. Veritabanına kaydet
        $flag = ProfileFlag::create([
            'reporter_id' => $authUserId,
            'flagged_profile_id' => $profileId,
        ]);

        // 2. İlişkili verileri yükle (Kullanıcı ve Profil bilgileri)
        // Modelde tanımladığımız 'reporter' ve 'flaggedProfile' fonksiyonlarını çağırır.
        $flag->load(['reporter', 'flaggedProfile']);

        // 3. Admin'e Email Gönder
        $adminEmail = 'admin@siteniz.com';
        Mail::to($adminEmail)->send(new ProfileFlaggedMail($flag));

        return $flag;
    }
}
