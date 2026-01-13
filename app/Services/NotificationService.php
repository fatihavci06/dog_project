<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    protected $oneSignal;

    public function __construct()
    {
        // Paylaştığınız sınıfın constructor yapısına göre:
        // __construct($appId, $restApiKey, $userAuthKey, ...)
        $this->oneSignal = new OneSignalClient(
            env('ONESIGNAL_APP_ID'),
            env('ONESIGNAL_API_KEY'),
            env('ONESIGNAL_USER_AUTH_KEY')
        );
    }

    public function sendNotification(array $data)
    {
        // 1️⃣ Notification kaydı
        $notification = Notification::create([
            'title'   => $data['title'],
            'message' => $data['message'],
            'url'     => $data['url'] ?? null,
        ]);

        $userIds = $data['user_ids'] ?? [];
        $roleIds = $data['role_ids'] ?? [];

        /**
         * 2️⃣ ROLE KAYDI
         */
        if (!empty($roleIds)) {
            $notification->roles()->sync($roleIds);
        }

        /**
         * 3️⃣ ROLE → USER ÇÖZÜMLEME
         */
        if (!empty($roleIds)) {
            $roleUserIds = User::whereHas('roles', function ($q) use ($roleIds) {
                $q->whereIn('roles.id', $roleIds);
            })->pluck('id')->toArray();

            $userIds = array_unique(array_merge($userIds, $roleUserIds));
        }

        /**
         * 4️⃣ USER PIVOT
         */
        if (!empty($userIds)) {
            $notification->users()->syncWithPivotValues(
                $userIds,
                ['sent_at' => now()]
            );
        }

        /**
         * 5️⃣ OneSignal
         * - Eğer global ise → tüm playerlar
         * - Değilse → hedeflenen kullanıcılar
         */
        $players = empty($userIds)
            ? User::whereNotNull('onesignal_player_id')->pluck('onesignal_player_id')->toArray()
            : User::whereIn('id', $userIds)
            ->whereNotNull('onesignal_player_id')
            ->pluck('onesignal_player_id')
            ->toArray();

        if (!empty($players)) {
            $this->oneSignal->sendNotificationCustom([
                'include_player_ids' => $players,
                'headings' => ['en' => $data['title']],
                'contents' => ['en' => $data['message']],
                'url'      => $data['url'] ?? null
            ]);
        }

        return $notification;
    }


    public function setOneSignalPlayerId(array $data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->update([
                'onesignal_player_id' => $data['onesignal_player_id'],
            ]);
        }
    }

    public function changeNotificationStatus($userId, $status)
    {
        $user = User::find($userId);
        if ($user) {
            $user->notification_status = $status;
            $user->save();
        }
    }
    public function getUserNotifications(
    int $userId,
    int $roleId,
    ?bool $isRead = null,
    int $page = 1,
    int $perPage = 10,
    bool $onlyUnread = false
): array {

    $userCreatedAt = User::where('id', $userId)->value('created_at');

    $query = Notification::query()
        ->leftJoin('notification_user as nu', function ($join) use ($userId) {
            $join->on('nu.notification_id', '=', 'notifications.id')
                ->where('nu.user_id', $userId);
        })

        // 🔥 ASIL FİLTRE BURASI
        ->where(function ($q) use ($userId, $roleId, $userCreatedAt) {

            // User’a atanmışsa → her zaman göster
            $q->whereExists(function ($sub) use ($userId) {
                $sub->selectRaw(1)
                    ->from('notification_user')
                    ->whereColumn('notification_user.notification_id', 'notifications.id')
                    ->where('notification_user.user_id', $userId);
            })

            // Role atanmışsa → kayıt tarihinden sonra
            ->orWhere(function ($sub) use ($roleId, $userCreatedAt) {
                $sub->whereExists(function ($r) use ($roleId) {
                    $r->selectRaw(1)
                        ->from('notification_role')
                        ->whereColumn('notification_role.notification_id', 'notifications.id')
                        ->where('notification_role.role_id', $roleId);
                })
                ->where('notifications.created_at', '>=', $userCreatedAt);
            })

            // Genel → kayıt tarihinden sonra
            ->orWhere(function ($sub) use ($userCreatedAt) {
                $sub->whereNotExists(function ($n) {
                    $n->selectRaw(1)
                        ->from('notification_user')
                        ->whereColumn('notification_user.notification_id', 'notifications.id');
                })
                ->whereNotExists(function ($n) {
                    $n->selectRaw(1)
                        ->from('notification_role')
                        ->whereColumn('notification_role.notification_id', 'notifications.id');
                })
                ->where('notifications.created_at', '>=', $userCreatedAt);
            });
        });

    // 🔹 Okunma filtresi
    if ($onlyUnread || $isRead === false) {
        $query->where(function ($q) {
            $q->whereNull('nu.is_read')
              ->orWhere('nu.is_read', false);
        });
    } elseif ($isRead === true) {
        $query->where('nu.is_read', true);
    }

    $paginator = $query->select([
            'notifications.id',
            'notifications.title',
            'notifications.type',
            'notifications.message',
            'notifications.url',
            'notifications.created_at',
            'nu.sent_at',
            'nu.is_read',
        ])
        ->distinct()
        ->orderByDesc(DB::raw('COALESCE(nu.sent_at, notifications.created_at)'))
        ->paginate($perPage, ['*'], 'page', $page);

    return [
        'current_page' => $paginator->currentPage(),
        'per_page'     => $paginator->perPage(),
        'total'        => $paginator->total(),
        'last_page'    => $paginator->lastPage(),
        'data'         => $paginator->items(),
    ];
}

    public function markAsRead(int $userId, int $notificationId): bool
    {
        // 1. Önce bildirimin gerçekten var olup olmadığını kontrol edelim
        $exists = Notification::where('id', $notificationId)->exists();
        if (!$exists) {
            return false;
        }

        // 2. Pivot tabloyu güncelle veya yeni kayıt oluştur (Upsert)
        DB::table('notification_user')->updateOrInsert(
            [
                'user_id' => $userId,
                'notification_id' => $notificationId
            ],
            [
                'is_read' => true,
            ]
        );

        return true;
    }
}
