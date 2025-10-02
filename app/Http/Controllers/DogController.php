<?php

namespace App\Http\Controllers;

use App\Services\DogService;
use Illuminate\Http\Request;

class DogController  extends ApiController
{
     protected $dogService;
    public function __construct(DogService $dogService)
    {
        $this->dogService = $dogService;
    }
    public function myList(Request $request)
    {
        return $this->dogService->getList($request->all());
    }
}
