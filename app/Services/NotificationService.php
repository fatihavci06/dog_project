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
        int $page = 1,
        int $perPage = 10,
        bool $onlyUnread = false
    ): array {

        $userRoleIds = DB::table('role_user')
            ->where('user_id', $userId)
            ->pluck('role_id');

        $query = Notification::query()
            ->leftJoin('notification_user as nu', function ($join) use ($userId) {
                $join->on('nu.notification_id', '=', 'notifications.id')
                    ->where('nu.user_id', '=', $userId);
            })
            ->leftJoin('notification_role as nr', function ($join) use ($userRoleIds) {
                $join->on('nr.notification_id', '=', 'notifications.id')
                    ->whereIn('nr.role_id', $userRoleIds);
            })
            ->where(function ($q) {
                // GLOBAL
                $q->whereNull('nu.notification_id')
                    ->whereNull('nr.notification_id');
            })
            ->orWhereNotNull('nu.notification_id') // USER
            ->orWhereNotNull('nr.notification_id') // ROLE
            ->select([
                'notifications.id',
                'notifications.title',
                'notifications.message',
                'notifications.url',
                'notifications.created_at',
                'nu.sent_at',
                'nu.is_read',
            ])
            ->orderByDesc(DB::raw('COALESCE(nu.sent_at, notifications.created_at)'));

        if ($onlyUnread) {
            $query->where(function ($q) {
                $q->whereNull('nu.is_read')->orWhere('nu.is_read', false);
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
            'data'         => $paginator->items(),
        ];
    }
}
