<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ApiLanguageController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFlagRequest;
use App\Services\FlagService;

class ProfileFlagController extends ApiController
{
    protected $service;

    public function __construct(FlagService $service) {
        $this->service = $service;
    }

    public function store(StoreFlagRequest $request)
    {
             $this->service->flagProfile($request->user_id, $request->flagged_profile_id);
    }
}
