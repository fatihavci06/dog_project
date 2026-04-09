<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Jobs\SendOneSignalNotification;
use App\Models\Conversation;
use App\Models\DiscoverBlackList;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\PupProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function getMessages(int $conversationId, int $userId, int $page = 1, int $perPage = 15, bool $paginate = false)
    {
        $conversation = Conversation::select('user_one_id', 'user_two_id')->findOrFail($conversationId);

        if (!in_array($userId, [$conversation->user_one_id, $conversation->user_two_id])) {
            throw new \Exception('forbidden');
        }

        $query = Message::join('users', 'messages.sender_id', '=', 'users.id')
            ->where('messages.conversation_id', $conversationId)
            ->select('messages.sender_id', 'messages.receiver_id', 'messages.created_at', 'messages.body', 'users.name as sender_name')
            ->orderBy('messages.created_at', 'desc');

        if ($paginate) {
            $messages = $query->paginate($perPage, ['*'], 'page', $page);
            $items = $messages->getCollection()->map(function ($msg) {
                return [
                    'sender_id' => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'sender_name' => $msg->sender_name,
                    'created_at' => $msg->created_at,
                    'body' => $msg->body,
                ];
            });
            $messages->setCollection($items);
            return $messages;
        }

        // Güvenlik Duvarı: Sayfalama yoksa en fazla son 300 mesajı getir. Sunucuyu çökertmemek için.
        $messages = $query->limit(300)->get();

        return $messages->map(function ($msg) {
            return [
                'sender_id' => $msg->sender_id,
                'receiver_id' => $msg->receiver_id,
                'sender_name' => $msg->sender_name,
                'created_at' => $msg->created_at,
                'body' => $msg->body,
            ];
        });
    }

    public function sendMessage(int $fromUserId, int $toUserId, ?string $body)
    {
        $min = min($fromUserId, $toUserId);
        $max = max($fromUserId, $toUserId);

        // Cache Anahtarları (Redis İçin)
        $blockCacheKey = "chat:block_status_{$fromUserId}_{$toUserId}";
        $convCacheKey = "chat:conversation_{$min}_{$max}";

        // 1. Engelleme Kontrolü (Redis Önbellekli)
        // Her seferinde DB EXITS sorgusu yatırmaktansa, sonucu 1 saatliğine Redis'te tutuyoruz.
        $isBlocked = \Illuminate\Support\Facades\Cache::remember($blockCacheKey, now()->addMinutes(60), function () use ($fromUserId, $toUserId) {
            $senderBlockedReceiver = \App\Models\DiscoverBlackList::where('user_id', $fromUserId)
                ->whereExists(function ($query) use ($toUserId) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('pup_profiles')
                        ->whereColumn('pup_profiles.id', 'discover_blacklists.pup_profile_id')
                        ->where('pup_profiles.user_id', $toUserId);
                })->exists();

            if ($senderBlockedReceiver) {
                return true;
            }

            return \App\Models\DiscoverBlackList::where('user_id', $toUserId)
                ->whereExists(function ($query) use ($fromUserId) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('pup_profiles')
                        ->whereColumn('pup_profiles.id', 'discover_blacklists.pup_profile_id')
                        ->where('pup_profiles.user_id', $fromUserId);
                })->exists();
        });

        if ($isBlocked) {
            throw new \Exception(__('errors.cannot_send_message_blocked'), 403);
        }

        // 2. Sohbet (Conversation) Bul/Oluştur (Redis Önbellekli)
        // firstOrCreate içindeki SELECT sorgusundan dahi tasarruf edip, Sohbet ID'sini 7 gün Redis'te tutuyoruz.
        $convId = \Illuminate\Support\Facades\Cache::remember($convCacheKey, now()->addDays(7), function () use ($min, $max) {
            $conv = Conversation::firstOrCreate([
                'user_one_id' => $min,
                'user_two_id' => $max
            ]);
            return $conv->id;
        });

        // 3. Mesajı Kaydet (Veritabanındaki TEK I/O İşlemi!)
        $message = Message::create([
            'conversation_id' => $convId,
            'sender_id' => $fromUserId,
            'receiver_id' => $toUserId,
            'body' => $body,
            'status' => 'sent'
        ]);

        // 4. Asenkron (Queued) Broadcast Tetikleme
        event(new MessageSent($message));

        // 5. Alıcı Push Bildirim Verilerini Sadece Gerekli Sütunlarla Çekme
        $to = User::select('id', 'onesignal_player_id', 'preferred_language')->find($toUserId);

        if ($to && !empty($to->onesignal_player_id)) {
            $currentLocale = app()->getLocale();

            app()->setLocale($to->preferred_language ?? config('app.locale'));

            dispatch(new SendOneSignalNotification(
                [$to->onesignal_player_id],
                __('notifications.new_message'),
                mb_strimwidth((string) $body, 0, 100),
                [
                    'conversation_id' => $convId,
                    'type' => 'message',
                    'url' => "pupcrawl://chat/{$convId}" // Dinamik link
                ]
            ));

            app()->setLocale($currentLocale ?? config('app.locale'));
        }

        return $message;
    }

    public function getInbox(int $userId, bool $excludeBlacklisted = false)
    {
        $myPupProfileIds = PupProfile::where('user_id', $userId)->pluck('id');

        // Kara listeleri hesapla (Sadece id'ler üzerinden, son derece hafiftir)
        $blockedByMeUserIds = DiscoverBlackList::where('user_id', $userId)
            ->join('pup_profiles', 'pup_profiles.id', '=', 'discover_blacklists.pup_profile_id')
            ->pluck('pup_profiles.user_id');

        $blockedMeUserIds = DiscoverBlackList::whereIn('pup_profile_id', $myPupProfileIds)
            ->pluck('user_id');

        $blacklistedUserIds = $blockedByMeUserIds->merge($blockedMeUserIds)->unique()->values()->all();

        // Eşleşme (Match) Kullanıcılarını Hızla Hesapla
        $matchUserIds = [];
        if ($myPupProfileIds->isNotEmpty()) {
            $otherPupIds = Friendship::where('status', 'accepted')
                ->where(function ($q) use ($myPupProfileIds) {
                    $q->whereIn('sender_id', $myPupProfileIds)
                        ->orWhereIn('receiver_id', $myPupProfileIds);
                })
                ->get(['sender_id', 'receiver_id'])
                ->flatMap(function ($f) use ($myPupProfileIds) {
                    return in_array($f->sender_id, $myPupProfileIds->toArray()) ? [$f->receiver_id] : [$f->sender_id];
                })
                ->unique()
                ->toArray();
                
            if (!empty($otherPupIds)) {
                $matchUserIds = PupProfile::whereIn('id', $otherPupIds)->pluck('user_id')->unique()->toArray();
            }
        }

        // Asıl Optimizasyon: N+1 Sorunsuz, Eager Loading Destekli ve SQL Count'lu Ana Sorgu
        $conversationsQuery = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with([
                'userOne.pupProfiles.images', 
                'userTwo.pupProfiles.images',
                'messages' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])
            // SQL ile unread_count çekimini PHP döngüsü yerine direkt Query'e entegre ediyoruz:
            ->addSelect(['*']) // Standart kolonlar
            ->addSelect([
                'unread_count' => Message::selectRaw('count(*)')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->where('receiver_id', $userId)
                    ->whereNull('read_at')
            ])
            ->orderByDesc('updated_at')
            ->take(100); // Sistemin ram/bellek taşmasından çökmesini engelleyen Güvenlik Bariyeri

        $conversations = $conversationsQuery->get();

        $result = $conversations->map(function ($conv) use ($userId, $blacklistedUserIds, $matchUserIds) {
            
            // DİKKAT: Mobil APP'in çökme sebebi === (Triple Equal) kullanımıydı!
            // Çünkü MySQL ID'leri PDO ile string '5' veya int 5 olarak karışık çevrilebiliyor. 
            // `==` kullanımıyla bu uyumsuzluğu kökünden çözdük.
            $otherUser = $conv->user_one_id == $userId ? $conv->userTwo : $conv->userOne;

            if (!$otherUser) {
                return null;
            }

            $isBlackList = in_array($otherUser->id, $blacklistedUserIds);
            $isMatch = in_array($otherUser->id, $matchUserIds);

            // MySQL "Count" sonucu string dönebileceğinden Mobile app'in bekledeği int casting yapıldı
            $unreadCount = (int) ($conv->unread_count ?? 0);
            $lastMessage = $conv->messages->first();

            return [
                'conversation_id' => $conv->id,
                'is_black_list' => $isBlackList,
                'is_match' => $isMatch,
                'user' => [
                    'id' => $otherUser->id,
                    'name' => $otherUser->name,
                    'avatar' => $otherUser->photo_url ?? null,
                ],
                'pup_profiles' => $otherUser->pupProfiles->take(1)->map(function ($pup) {
                    return [
                        'id' => $pup->id,
                        'name' => $pup->name,
                        'images' => $pup->images->take(1)->map(fn($img) => $img->path)->toArray(),
                    ];
                })->toArray(),
                'last_message' => $lastMessage ? [
                    'id' => $lastMessage->id,
                    'conversation_id' => $lastMessage->conversation_id,
                    'sender_id' => $lastMessage->sender_id,
                    'receiver_id' => $lastMessage->receiver_id,
                    'body' => $lastMessage->body,
                    'status' => $lastMessage->status,
                    'created_at' => $lastMessage->created_at,
                    'updated_at' => $lastMessage->updated_at,
                ] : null,
                'unread_count' => $unreadCount,
                'updated_at' => $conv->updated_at,
            ];
        })->filter()->values();

        if ($excludeBlacklisted && !empty($blacklistedUserIds)) {
            $result = $result->reject(function ($item) {
                return $item['is_black_list'] === true;
            })->values();
        }

        return $result;
    }

    public function markRead(int $conversationId, int $userId)
    {
        $conv = Conversation::findOrFail($conversationId);

        if (!in_array($userId, [$conv->user_one_id, $conv->user_two_id])) {
            throw new \Exception('forbidden');
        }

        Message::where('conversation_id', $conversationId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'status' => 'read']);

        return true;
    }

    public function getAllMessages(int $conversationId, int $userId, int $perPage = 50, int $page = 1)
    {
        $user = User::findOrFail($userId);
        $conversation = Conversation::findOrFail($conversationId);

        if (!in_array($user->id, [$conversation->user_one_id, $conversation->user_two_id]) && !$user->is_admin) {
            throw new \Exception('forbidden');
        }

        return Message::with('sender', 'receiver')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
    public function getLatestMessages()
    {
        $userId = Auth::id();

        if (!$userId) {
            return [
                'error' => 'Unauthorized',
                'status' => 401,
            ];
        }

        $messages = Message::with('sender')
            ->where('receiver_id', $userId)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'body' => $msg->body,
                    'conversation_id' => $msg->conversation_id,
                    'sender' => [
                        'name' => $msg->sender->name ?? 'Unknown',
                        'profile_photo_url' => $msg->sender->profile_photo_url ?? asset('storage/profile.jpg'),
                    ],
                    'created_at' => $msg->created_at->toDateTimeString(),
                ];
            });

        $unreadCount = Message::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->count();

        return [
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'status' => 200,
        ];
    }
    public function show(int $conversationId)
    {
        return Message::where('conversation_id', $conversationId)
            ->with('sender:id,name')
            ->orderBy('created_at', 'asc')
            ->take(50)
            ->get();
    }
    public function startConversation(array $data)
    {
        $senderId = Auth::id();
        $receiverId = $data['recipient_id'];
        $body = $data['body'];

        // 1️⃣ Mevcut sohbeti kontrol et
        $conversation = Conversation::where(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $senderId)
                ->where('user_two_id', $receiverId);
        })->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('user_one_id', $receiverId)
                ->where('user_two_id', $senderId);
        })->first();

        // 2️⃣ Sohbet yoksa, yeni oluştur
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $senderId,
                'user_two_id' => $receiverId,
            ]);
        }

        // 3️⃣ Mesajı oluştur
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'body' => $body,
        ]);

        // 4️⃣ JSON formatında döndür
        return [
            'success' => true,
            'message' => $message,
            'conversation_id' => $conversation->id,
        ];
    }
    public function getChatIndexData(?int $selectedConversationId = null, int $perPage = 15)
    {
        $userId = Auth::id();

        // 1️⃣ Kullanıcının mevcut sohbetleri
        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['messages' => fn($q) => $q->latest()])
            ->get();

        // 2️⃣ Diğer kullanıcılar (sayfalı)
        $allOtherUsers = User::where('id', '!=', $userId)
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        // 3️⃣ Seçili sohbet varsa mesajları al
        $selectedConversation = null;
        $messages = collect();

        if ($selectedConversationId) {
            $selectedConversation = $selectedConversationId;
            $messages = Message::where('conversation_id', $selectedConversation)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return compact('conversations', 'selectedConversation', 'messages', 'allOtherUsers');
    }
    public function loadMoreMessages(int $conversationId, int $lastMessageId, int $take = 20)
    {
        return Message::where('conversation_id', $conversationId)
            ->where('id', '<', $lastMessageId)
            ->with('sender:id,name')
            ->orderByDesc('created_at')
            ->take($take)
            ->get()
            ->reverse() // eski mesajları başa ekle
            ->values();
    }
    public function getUserPupProfileList(int $userId, int $page = 1, int $perPage = 10)
    {
        /*
         |--------------------------------------------------------------------------
         | CHAT USER IDS
         |--------------------------------------------------------------------------
         */
        $chatUserIds = Message::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        })
            ->get()
            ->map(function ($message) use ($userId) {
                return $message->sender_id == $userId
                    ? $message->receiver_id
                    : $message->sender_id;
            })
            ->unique()
            ->values();

        /*
         |--------------------------------------------------------------------------
         | BLACKLIST USERS (Inbox ile aynı mantık)
         |--------------------------------------------------------------------------
         */
        $myPupProfileIds = PupProfile::where('user_id', $userId)->pluck('id');

        $blockedByMeUserIds = PupProfile::whereIn(
            'id',
            DiscoverBlackList::where('user_id', $userId)->pluck('pup_profile_id')
        )->pluck('user_id');

        $blockedMeUserIds = DiscoverBlackList::whereIn(
            'pup_profile_id',
            $myPupProfileIds
        )->pluck('user_id');

        $blacklistedUserIds = $blockedByMeUserIds
            ->merge($blockedMeUserIds)
            ->unique()
            ->values()
            ->all();

        /*
         |--------------------------------------------------------------------------
         | MATCH USERS
         |--------------------------------------------------------------------------
         */
        $matchUserIds = [];

        if ($chatUserIds->isNotEmpty() && $myPupProfileIds->isNotEmpty()) {

            $otherPupProfiles = PupProfile::whereIn('user_id', $chatUserIds)
                ->get(['id', 'user_id']);

            $otherPupProfileIds = $otherPupProfiles->pluck('id');

            if ($otherPupProfileIds->isNotEmpty()) {

                $pupIdToUserId = PupProfile::whereIn(
                    'id',
                    $myPupProfileIds->merge($otherPupProfileIds)
                )->pluck('user_id', 'id');

                $friendships = Friendship::query()
                    ->where('status', 'accepted')
                    ->where(function ($q) use ($myPupProfileIds, $otherPupProfileIds) {
                        $q->where(
                            function ($q2) use ($myPupProfileIds, $otherPupProfileIds) {
                                $q2->whereIn('sender_id', $myPupProfileIds)
                                    ->whereIn('receiver_id', $otherPupProfileIds);
                            }
                        )->orWhere(
                                function ($q2) use ($myPupProfileIds, $otherPupProfileIds) {
                                    $q2->whereIn('sender_id', $otherPupProfileIds)
                                        ->whereIn('receiver_id', $myPupProfileIds);
                                }
                            );
                    })
                    ->get(['sender_id', 'receiver_id']);

                $matchUserIds = $friendships
                    ->map(function ($f) use ($pupIdToUserId, $userId) {

                        $senderUserId = $pupIdToUserId[$f->sender_id] ?? null;
                        $receiverUserId = $pupIdToUserId[$f->receiver_id] ?? null;

                        if (!$senderUserId || !$receiverUserId) {
                            return null;
                        }

                        return $senderUserId === $userId
                            ? $receiverUserId
                            : $senderUserId;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        /*
         |--------------------------------------------------------------------------
         | USER LIST
         |--------------------------------------------------------------------------
         */
        $mapped = User::with([
            'pupProfiles' => function ($q) use ($userId) {
                $q->select('id', 'user_id', 'name')
                    ->where('user_id', '!=', $userId)
                    ->with([
                        'images' => function ($q) {
                            $q->select('id', 'pup_profile_id', 'path');
                        }
                    ]);
            }
        ])
            ->whereIn('id', $chatUserIds)
            ->select('id', 'name', 'photo')
            ->get()
            ->filter(fn($user) => $user->pupProfiles->isNotEmpty())
            ->map(function ($user) use ($blacklistedUserIds, $matchUserIds) {

                $user->user_id = $user->id;
                $user->makeHidden('photo');

                // ✅ EKLENEN ALANLAR
                $user->is_black_list = in_array($user->id, $blacklistedUserIds, true);
                $user->is_match = in_array($user->id, $matchUserIds, true);

                $user->pupProfiles->each(
                    function ($pup) {
                        $pup->makeHidden('user_id');

                        $pup->images->each(
                            function ($image) {
                                $image->makeHidden(['id', 'pup_profile_id']);
                            }
                        );
                    }
                );

                return $user;
            })
            ->values();

        /*
         |--------------------------------------------------------------------------
         | PAGINATION
         |--------------------------------------------------------------------------
         */
        $total = $mapped->count();
        $lastPage = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $paged = $mapped->slice($offset, $perPage)->values();

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'data' => $paged,
        ];
    }
}
