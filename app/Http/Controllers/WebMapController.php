<?php

namespace App\Http\Controllers;

use App\Services\MapService;
use Illuminate\Http\Request;

class WebMapController extends Controller
{
    protected MapService $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }
}
