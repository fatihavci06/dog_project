<?php

namespace App\Http\Controllers;

use App\Http\Requests\DogRequest;
use App\Http\Requests\DogShowDeleteRequest;
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
    public function delete(DogShowDeleteRequest $request)
    {
        return $this->dogService->delete($request->all());
    }
    public function show(DogShowDeleteRequest $request)
    {
        return $this->dogService->show($request->all());
    }

    public function update(DogRequest $request)
    {
        return $this->dogService->update($request->all());
    }
}
