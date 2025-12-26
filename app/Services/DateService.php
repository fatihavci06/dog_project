<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Date;
use Carbon\Carbon;
use Exception;
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
        $paginator = Date::query()
            ->where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->orderBy('meeting_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function (Date $date) use ($userId) {

            $conversationId = Conversation::query()
                ->where(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $userId)
                        ->where('user_two_id', $date->sender_id);
                })
                ->orWhere(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $date->sender_id)
                        ->where('user_two_id', $userId);
                })
                ->value('id'); // 🔥 sadece id

            return [
                'id'           => $date->id,
                'meeting_date' => $date->meeting_date,
                'status'       => $date->status,
                'is_flexible'  => $date->is_flexible,
                'address'      => $date->address,
                'description'  => $date->description,

                'sender' => $date->sender,

                'conversation_id' => $conversationId,
            ];
        })->values();

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $data,
        ];
    }

    public function getApprovedDateById(int $userId, int $dateId): array
    {
        $date = Date::with(['sender', 'receiver'])
            ->where('id', $dateId)
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->first();
        if (empty($date)) {

            throw new Exception('Not found.', 404);
        }

        return [
            'id'           => $date->id,
            'meeting_date' => $date->meeting_date, // accessor varsa otomatik formatlı
            'status'       => $date->status,
            'sender'       => $date->sender,
            'receiver'     => $date->receiver,
        ];
    }

    public function getApprovedDates(int $userId, int $page = 1, int $perPage = 10): array
    {
        $paginator = Date::query()
            ->with(['sender', 'receiver'])
            ->where('status', 'accepted')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->orderBy('meeting_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function (Date $date) use ($userId) {

            // 🔥 Karşı taraf user_id
            $otherUserId = $date->sender_id === $userId
                ? $date->receiver_id
                : $date->sender_id;

            // 🔥 conversation_id bul
            $conversationId = Conversation::query()
                ->where(function ($q) use ($userId, $otherUserId) {
                    $q->where('user_one_id', $userId)
                        ->where('user_two_id', $otherUserId);
                })
                ->orWhere(function ($q) use ($userId, $otherUserId) {
                    $q->where('user_one_id', $otherUserId)
                        ->where('user_two_id', $userId);
                })
                ->value('id');

            // 🔥 Modeli bozmadan sadece alan ekle
            $dateArray = $date->toArray();
            $dateArray['conversation_id'] = $conversationId;

            return $dateArray;
        })->values();

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $data,
        ];
    }




    /**
     * Kullanıcının gönderdiği istekleri listeler (Sayfalı).
     * Bekleyen, onaylanan veya reddedilen tüm geçmişi görür.
     */
    public function getOutgoingRequests(int $userId, int $page = 1, int $perPage = 10): array
    {
        $paginator = Date::query()
            ->where('sender_id', $userId)
            ->with('receiver')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function (Date $date) use ($userId) {

            $conversationId = Conversation::query()
                ->where(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $userId)
                        ->where('user_two_id', $date->receiver_id);
                })
                ->orWhere(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $date->receiver_id)
                        ->where('user_two_id', $userId);
                })
                ->value('id'); // 🔥 sadece id

            return [
                'id'           => $date->id,
                'meeting_date' => $date->meeting_date,
                'status'       => $date->status,
                'is_flexible'  => $date->is_flexible,
                'address'      => $date->address,
                'description'  => $date->description,

                'receiver' => $date->receiver,

                'conversation_id' => $conversationId,
            ];
        })->values();

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $data,
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
    public function deleteOutgoingPendingDate(int $userId, int $dateId): void
    {
        $date = Date::where('id', $dateId)
            ->where('status', 'pending')
            ->where('sender_id', $userId)
            ->first();

        if (!$date) {
            throw new Exception('Not found or unauthorized.', 404);
        }

        $date->delete();
    }
    public function getOutgoingPendingDateForEdit(int $userId, int $dateId)
    {
        $date = Date::with('receiver')
            ->where('id', $dateId)
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->first();

        if (!$date) {
            throw new Exception('Pending Request Not Found ', 404);
        }

        return $date;
    }
    public function updateOutgoingPendingDate(
        int $userId,
        int $dateId,
        array $data
    ) {
        $date = Date::where('id', $dateId)
            ->where('status', 'pending')
            ->where('sender_id', $userId)
            ->first();

        $date->update([
            'meeting_date' => Carbon::parse($data['meeting_date']),
            'is_flexible'  => $data['is_flexible'],
            'address'      => $data['address'],
            'latitude'     => $data['latitude'],
            'longitude'    => $data['longitude'],
            'description' => $data['description']
        ]);
        return $date;
    }
}
