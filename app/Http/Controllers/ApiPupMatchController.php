<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlackListAddRequest;
use App\Models\DiscoverBlackList;
use App\Services\PupMatchmakingService;
use Illuminate\Http\Request;

class ApiPupMatchController extends ApiController
{
    public function matches(Request $request, int $pupProfileId, PupMatchmakingService $service)
    {
        $page     = (int) $request->get('page', 1);
        $perPage  = (int) $request->get('per_page', 10);

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
            $request->user_id
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function blackListAdd(BlackListAddRequest $request)
    {


        return  DiscoverBlackList::firstOrCreate([
            'user_id' => $request->user_id,
            'pup_profile_id' => $request->pup_profile_id
        ]);
    }
    public function getBlacklist(Request $request)
    {
        $authUserId = $request->user_id;
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $offset = ($page - 1) * $perPage;

        // Gizlenen profilleri getir
        $query = DiscoverBlackList::with('pupProfile.images')
            ->where('user_id', $authUserId)
            ->latest();

        $total = $query->count();
        $lastPage = ceil($total / $perPage);

        // Veriyi çek ve istenen formata dönüştür
        $data = $query->skip($offset)
            ->take($perPage)
            ->get()
            ->map(fn($item) => [
                'blacklist_id'   => $item->id,
                'pup_profile_id' => $item->pupProfile->id,
                'name'           => $item->pupProfile->name,
                'photo'          => $item->pupProfile->images[0]->path ?? null,
                'added_at'       => $item->created_at->format('Y-m-d H:i'),
            ]);

        return [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
            'last_page'    => $lastPage,
            'data'         => $data->values()->toArray(),
        ];
    }
    public function removeBlacklist(Request $request, $pupProfileId)
    {
        $authUserId = $request->user_id;

         DiscoverBlackList::where('user_id', $authUserId)
            ->where('pup_profile_id', $pupProfileId)
            ->delete();

    }
}
