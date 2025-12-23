<?php

namespace App\Services;

use App\Models\Date;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DateService
{
    /**
     * Kullanıcıya gelen buluşma isteklerini listeler (Sayfalı).
     * Sadece 'pending' (bekleyen) istekler gösterilir.
     */
    public function getIncomingRequests(int $userId, int $page = 1, int $perPage = 10): array
    {
        $paginator = Date::with('sender')
            ->where('receiver_id', $userId)
            ->where('status', 'pending')
            ->orderBy('meeting_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $paginator->items(),
        ];
    }
    public function getApprovedDates(int $userId, int $page = 1, int $perPage = 10): array
    {
        $paginator = Date::with(['sender', 'receiver'])
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->orderBy('meeting_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $paginator->items(),
        ];
    }


    /**
     * Kullanıcının gönderdiği istekleri listeler (Sayfalı).
     * Bekleyen, onaylanan veya reddedilen tüm geçmişi görür.
     */
    public function getOutgoingRequests(int $userId, int $page = 1, int $perPage = 10): array
    {
        $paginator = Date::with('receiver')
            ->where('sender_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $paginator->items(),
        ];
    }

    /**
     * Yeni bir Date isteği oluşturur.
     * UI'dan tarih ve saat ayrı gelir, burada birleştirilip kaydedilir.
     */
    public function createDate(int $senderId, array $data): Date
    {
        // 1. Frontend'den gelen 'date' (Y-m-d) ve 'time' (H:i) bilgisini birleştir
        try {
            $meetingDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['date'] . ' ' . $data['time']
            );
        } catch (\Exception $e) {
            throw new HttpException(400, 'Invalid date or time format.');
        }

        // 2. Geçmiş tarih kontrolü
        if ($meetingDateTime->isPast()) {
            throw new HttpException(400, 'You cannot select a past date and time.');
        }

        // 3. Spam/Mükerrer Kayıt Kontrolü:
        // Bu kişiye zaten bekleyen bir isteğin var mı?
        $exists = Date::where('sender_id', $senderId)
            ->where('receiver_id', $data['receiver_id'])
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            throw new HttpException(400, 'You already have a pending request for this user.');
        }

        // 4. Kayıt Oluşturma
        return Date::create([
            'sender_id'    => $senderId,
            'receiver_id'  => $data['receiver_id'],
            'meeting_date' => $meetingDateTime,      // Birleştirilmiş datetime
            'is_flexible'  => $data['is_flexible'] ?? false,
            'address'      => $data['address'] ?? null,
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
            'description'  => $data['description'] ?? null, // Opsiyonel not alanı
            'status'       => 'pending'
        ]);
    }

    /**
     * Gönderen (Sender) isteği iptal eder.
     * Sadece 'pending' durumundaysa iptal edilebilir.
     */
    public function cancelDate(int $dateId, int $userId): array
    {
        $date = Date::where('id', $dateId)->where('sender_id', $userId)->first();

        if (!$date) {
            throw new HttpException(404, 'Date request not found.');
        }

        if ($date->status !== 'pending') {
            throw new HttpException(400, 'Only pending requests can be cancelled.');
        }

        $date->delete();

        return ['message' => 'Request cancelled successfully.'];
    }

    /**
     * Alıcı (Receiver) isteği onaylar veya reddeder.
     * Status: 'accepted' veya 'rejected' olmalıdır.
     */
    public function respondDate(int $dateId, int $userId, string $status): Date
    {
        $date = Date::where('id', $dateId)->where('receiver_id', $userId)->first();

        if (!$date) {
            throw new HttpException(404, 'Date request not found.');
        }

        if ($date->status !== 'pending') {
            throw new HttpException(400, 'This request has already been processed.');
        }

        $date->update(['status' => $status]);

        return $date;
    }
}
