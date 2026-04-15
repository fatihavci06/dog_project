<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlackListAddRequest;
use App\Models\DiscoverBlackList;
use App\Services\PupMatchmakingService;
use Illuminate\Http\Request;

class ApiPupMatchController extends Controller
{
    public function matches(Request $request, int $pupProfileId, PupMatchmakingService $service)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        return $service->getMatchesPaginated(
            $pupProfileId,
            $request->user_id,
            $page,
            $perPage
        );
    }
    public function showProfile(Request $request, $pupProfileId, PupMatchmakingService $service)
    {
        $data = $service->getMatchDetail(
            $pupProfileId,
            $request->user_id,
            (int) ($request->my_pup_profile_id ?? 0)
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function blackListAdd(BlackListAddRequest $request)
    {
        // 1. Kara listeye ekleme işlemi
        $blackList = DiscoverBlackList::firstOrCreate([
            'user_id' => $request->user_id,
            'pup_profile_id' => $request->pup_profile_id
        ]);

        // 2. İşlemi yapan kullanıcının tüm kendi köpek profillerinin ID'lerini al
        $myPupProfileIds = \App\Models\PupProfile::where('user_id', $request->user_id)
            ->pluck('id')
            ->toArray();

        // 3. Kullanıcının köpekleri ile kara listeye eklenen köpek arasındaki arkadaşlıkları (Friendship) sil
        if (!empty($myPupProfileIds)) {
            \App\Models\Friendship::where(function ($query) use ($myPupProfileIds, $request) {
                $query->whereIn('sender_id', $myPupProfileIds)
                    ->where('receiver_id', $request->pup_profile_id);
            })->orWhere(function ($query) use ($myPupProfileIds, $request) {
                $query->where('sender_id', $request->pup_profile_id)
                    ->whereIn('receiver_id', $myPupProfileIds);
            })->delete();
        }
        \App\Models\Favorite::where('user_id', $request->user_id)
            ->where('favorite_id', $request->pup_profile_id)
            ->delete();

        // 4. Sonucu çok dilli (multi-language) olarak döndür
        return response()->json([
            'message' => __('messages.black_list_add'),
            'data' => $blackList
        ]);
    }
    public function getBlacklist(Request $request)
    {
        $authUserId = $request->user_id;
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;

        // Gizlenen profilleri getir
        $query = DiscoverBlackList::with(['pupProfile.images', 'pupProfile.user'])
            ->where('user_id', $authUserId)
            ->latest();

        $total = $query->count();
        $lastPage = ceil($total / $perPage);

        // Veriyi çek ve istenen formata dönüştür
        $data = $query->skip($offset)
            ->take($perPage)
            ->get()
            ->map(fn($item) => [
                'blacklist_id' => $item->id,
                'pup_profile_id' => $item->pupProfile->id,
                'name' => ($item->pupProfile->user->role_id == 4 && !$item->pupProfile->name) ? $item->pupProfile->user->name : $item->pupProfile->name,
                'photo' => ($item->pupProfile->user->role_id == 4) ? ($item->pupProfile->user->photo_url ?? null) : ($item->pupProfile->images[0]->path ?? null),
                'added_at' => $item->created_at->format('Y-m-d H:i'),
            ]);

        return [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage,
            'data' => $data->values()->toArray(),
        ];
    }
    public function removeBlacklist(Request $request, $pupProfileId)
    {
        $authUserId = $request->user_id;

        $data = DiscoverBlackList::where('user_id', $authUserId)
            ->where('pup_profile_id', $pupProfileId)
            ->delete();

        if ($data === 0) {
            return response()->json([
                'success' => false,
                'message' => __('messages.not_found'),
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.black_list_remove'),
        ], 200);
    }
}
