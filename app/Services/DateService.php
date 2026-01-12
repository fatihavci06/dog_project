<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Date;
use App\Models\Message;
use App\Models\PupProfile;
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
    public function getIncomingRequests(int $userId, int $page = 1, int $perPage = 10, int $pupProfileId = null): array
    {
        // 1️⃣ Kullanıcının pup profile id’leri

        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();

            if (!$ownsProfile) {
                throw new Exception(__('errors.cannot_access_pup_profile'), 403);
            }

            $pupProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $pupProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }

        // 2️⃣ Incoming: sender karşı taraf, receiver benim profillerim
        $paginator = Date::query()
            ->whereIn('receiver_id', $pupProfileIds)
            ->with([
                'sender.user'
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        // 3️⃣ Response mapping
        $data = collect($paginator->items())->map(function (Date $date) use ($userId) {

            $conversationId = Conversation::query()
                ->where(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $userId)
                        ->where('user_two_id', $date->sender->user->id);
                })
                ->orWhere(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $date->sender->user->id)
                        ->where('user_two_id', $userId);
                })
                ->value('id');

            return [
                'id'           => $date->id,
                'meeting_date' => $date->meeting_date,
                'status'       => $date->status,
                'is_flexible'  => $date->is_flexible,
                'address'      => $date->address,
                'description'  => $date->description,

                // 🔥 Incoming olduğu için sender dönüyoruz
                'sender' => $date->sender,
                'pup_profile_photo' => $date->receiver
                    ->images()
                    ->select('path')
                    ->value('path'),
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

    public function getApprovedDates(int $userId, int $page = 1, int $perPage = 10, int $pupProfileId = null): array
    {
        // 1️⃣ Kullanıcının pup profile id’leri
        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();

            if (!$ownsProfile) {
                throw new Exception(__('errors.cannot_access_pup_profile'), 403);
            }

            $pupProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $pupProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }

        // 2️⃣ Accepted date’ler (sender veya receiver benim profillerim)
        $paginator = Date::query()
            ->where('status', 'accepted')
            ->where(function ($q) use ($pupProfileIds) {
                $q->whereIn('sender_id', $pupProfileIds)
                    ->orWhereIn('receiver_id', $pupProfileIds);
            })
            ->with([
                'sender.user',
                'receiver.user',
            ])
            ->orderBy('meeting_date', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        // 3️⃣ Response mapping
        $data = collect($paginator->items())->map(function (Date $date) use ($pupProfileIds, $userId) {

            // 🔥 Karşı taraf pup profile
            $otherProfile = in_array($date->sender_id, $pupProfileIds)
                ? $date->receiver
                : $date->sender;

            // 🔥 Karşı taraf user_id
            $otherUserId = $otherProfile->user->id ?? null;

            // 🔥 conversation_id
            $conversationId = null;
            if ($otherUserId) {
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
            }

            return [
                'id'           => $date->id,
                'meeting_date' => $date->meeting_date,
                'status'       => $date->status,
                'is_flexible'  => $date->is_flexible,
                'address'      => $date->address,
                'description'  => $date->description,

                // 🔥 Benim dışımdaki taraf
                'other' => $otherProfile,
                'pup_profile_photo' => $otherProfile
                    ->images()
                    ->select('path')
                    ->value('path'),

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
     * Kullanıcının gönderdiği istekleri listeler (Sayfalı).
     * Bekleyen, onaylanan veya reddedilen tüm geçmişi görür.
     */
    public function getOutgoingRequests(int $userId, int $page = 1, int $perPage = 10, int $pupProfileId = null): array
    {
        if ($pupProfileId) {

            // Güvenlik: Bu pup profile kullanıcıya mı ait?
            $ownsProfile = PupProfile::where('id', $pupProfileId)
                ->where('user_id', $userId)
                ->exists();

            if (!$ownsProfile) {
                throw new Exception(__('errors.cannot_access_pup_profile'), 403);
            }

            $pupProfileIds = [$pupProfileId];
        } else {
            // Aksi halde kullanıcının tüm pup profilleri
            $pupProfileIds = PupProfile::where('user_id', $userId)
                ->pluck('id')
                ->toArray();
        }

        $paginator = Date::query()
            ->where(function ($q) use ($pupProfileIds) {
                $q->whereIn('sender_id', $pupProfileIds)
                    ->orWhereIn('receiver_id', $pupProfileIds);
            })
            ->with([
                'receiver.user'
            ])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $data = collect($paginator->items())->map(function (Date $date) use ($userId) {

            $conversationId = Conversation::query()
                ->where(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $userId)
                        ->where('user_two_id', $date->receiver->user->id);
                })
                ->orWhere(function ($q) use ($userId, $date) {
                    $q->where('user_one_id', $date->receiver->user->id)
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
                'pup_profile_photo' => $date->receiver
                    ->images()
                    ->select('path')
                    ->value('path'),
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
    public function createDate(int $senderId, array $data)
    {
        $myProfile = PupProfile::find($data['my_pup_profile_id']);
        $targetProfile = PupProfile::find($data['target_pup_profile_id']);
        // 1. Frontend'den gelen 'date' (Y-m-d) ve 'time' (H:i) bilgisini birleştir
        if ($myProfile->user_id === $targetProfile->user_id) {
            throw new HttpException(
                403,
                __('errors.cannot_date_own_profile')
            );
        }
        $myUserId = $myProfile->user_id;
        $targetUserId = $targetProfile->user_id;

        // 2️⃣ Daha önce mesajlaşma var mı kontrol et
        $hasConversation = Message::where(function ($q) use ($myUserId, $targetUserId) {
            $q->where('sender_id', $myUserId)
                ->where('receiver_id', $targetUserId);
        })->orWhere(function ($q) use ($myUserId, $targetUserId) {
            $q->where('sender_id', $targetUserId)
                ->where('receiver_id', $myUserId);
        })->exists();

        if (!$hasConversation) {
            throw new Exception(
                __('errors.no_conversation'),
                403
            );
        }

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



        // 4. Kayıt Oluşturma
        $newDate = Date::create([
            'sender_id'    => $data['my_pup_profile_id'],
            'receiver_id'  => $data['target_pup_profile_id'],
            'meeting_date' => $meetingDateTime,      // Birleştirilmiş datetime
            'is_flexible'  => $data['is_flexible'] ?? false,
            'address'      => $data['address'] ?? null,
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
            'description'  => $data['description'] ?? null, // Opsiyonel not alanı
            'status'       => 'pending'
        ]);
        $targetUser = $targetProfile->user;
        $currentLocale = app()->getLocale();

        // Hedef kullanıcının tercih ettiği dili set et
        if (!empty($targetUser->preferred_language)) {
            app()->setLocale($targetUser->preferred_language);
        }
        if ($targetUser && !empty($targetUser->onesignal_player_id)) {



            dispatch(new \App\Jobs\SendOneSignalNotification(
                [$targetUser->onesignal_player_id],
                __('notifications.date_request_title'),
                __('notifications.date_request_body', [
                    'name' => $myProfile->name
                ]),
                [
                    'date_id' => $newDate->id,
                    'type'    => 'date_request',
                    'url'     => "pupcrawl://dates/{$newDate->id}"
                ]
            ));
        }

        app()->setLocale($currentLocale);
        return $newDate;
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
        $myPupProfileIds = PupProfile::where('user_id', $userId)
            ->pluck('id')
            ->toArray();
        $date = Date::where('id', $dateId)
            ->whereIn('receiver_id', $myPupProfileIds)
            ->first();

        if (!$date) {
            throw new HttpException(404, 'Date request not found.');
        }

        if ($date->status !== 'pending') {
            throw new HttpException(400, 'This request has already been processed.');
        }

        $date->update(['status' => $status]);
        $senderProfile = \App\Models\PupProfile::find($date->sender_id);
        $targetUser = $senderProfile ? $senderProfile->user : null;

        // Yanıt veren kişinin profil adını alalım (Bildirimde kimin kabul ettiğini göstermek için)
        $responderProfile = \App\Models\PupProfile::find($date->receiver_id);
        $responderName = $responderProfile ? $responderProfile->name : 'Bir kullanıcı';
        $currentLocale = app()->getLocale();

        // Hedef kullanıcının tercih ettiği dili set et
        if (!empty($targetUser->preferred_language)) {
            app()->setLocale($targetUser->preferred_language);
        }

        if ($targetUser && !empty($targetUser->onesignal_player_id)) {




            $titleKey = $status === 'accepted'
                ? 'notifications.date_accepted_title'
                : 'notifications.date_rejected_title';

            $bodyKey = $status === 'accepted'
                ? 'notifications.date_accepted_body'
                : 'notifications.date_rejected_body';

            dispatch(new \App\Jobs\SendOneSignalNotification(
                [$targetUser->onesignal_player_id],
                __($titleKey),
                __($bodyKey, [
                    'name' => $responderName
                ]),
                [
                    'date_id' => $date->id,
                    'type'    => 'date_response',
                    'status'  => $status,
                    'url'     => "pupcrawl://date/{$date->id}"
                ]
            ));
        }
        app()->setLocale($currentLocale);

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
    public function getOutgoingPendingDateForEdit(int $userId, int $dateId): Date
    {
        // 1️⃣ Kullanıcının pup profile id’leri
        $pupProfileIds = PupProfile::where('user_id', $userId)
            ->pluck('id')
            ->toArray();

        // 2️⃣ Date kontrolü
        $date = Date::query()
            ->where('id', $dateId)
            ->where(function ($q) use ($pupProfileIds) {
                $q->whereIn('sender_id', $pupProfileIds)
                    ->orWhereIn('receiver_id', $pupProfileIds);
            })
            ->with([
                'sender.user',
                'receiver.user',
            ])
            ->first();

        if (!$date) {
            throw new Exception(' Request Not Found', 404);
        }
        $date->sender_pup_profile_photo = $date->sender
            ? $date->sender->images()->select('path')->value('path')
            : null;

        $date->receiver_pup_profile_photo = $date->receiver
            ? $date->receiver->images()->select('path')->value('path')
            : null;

        return $date;
    }

    public function updateOutgoingPendingDate(
        int $userId,
        int $dateId,
        array $data
    ): Date {
        // 1️⃣ Kullanıcının pup profile id’leri
        $pupProfileIds = PupProfile::where('user_id', $userId)
            ->pluck('id')
            ->toArray();

        // 2️⃣ Sadece bana ait + pending + outgoing olan date
        $date = Date::query()
            ->where('id', $dateId)
            ->where('status', 'pending')
            ->whereIn('sender_id', $pupProfileIds) // 🔥 outgoing
            ->first();

        if (!$date) {
            throw new Exception('Pending Date Not Found or Unauthorized', 404);
        }

        // 3️⃣ Update
        $date->update([
            'meeting_date' => Carbon::parse($data['meeting_date']),
            'is_flexible'  => (bool) $data['is_flexible'],
            'address'      => $data['address'] ?? null,
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $date;
    }
}
