<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavoriteRequest;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class ApiFavoriteController extends ApiController
{
    public function add(FavoriteRequest $request, FavoriteService $service)
    {
        return $service->add($request->user_id, $request->pup_profile_id);
    }

    public function remove(FavoriteRequest $request, FavoriteService $service)
    {
        return $service->remove($request->user_id, $request->pup_profile_id);
    }

    public function list(Request $request, FavoriteService $service)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        return $service->list($request->user_id, $page, $perPage);
    }
}
