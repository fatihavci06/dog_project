<?php

namespace App\Http\Controllers;

use App\Services\PupMatchmakingService;
use Illuminate\Http\Request;

class ApiPupMatchController extends Controller
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
    public function showProfile(Request $request,$pupProfileId, PupMatchmakingService $service)
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
}
