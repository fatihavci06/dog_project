<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\CalendarRequest;
use App\Http\Requests\CalendarShowDeleteRequest;
use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class ApiCalendarController extends ApiController
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    public function index(Request $request)
    {
       return $this->calendarService->index($request->all());

    }

    public function store(CalendarRequest $request)
    {
        return $this->calendarService->store($request->validated());

    }

    public function show(int $id,CalendarShowDeleteRequest $request)
    {
        return $this->calendarService->show($id,$request->all());

    }

    public function update(CalendarRequest $request, int $id)
    {
        return $this->calendarService->update($id, $request->validated());

    }

    public function destroy(CalendarShowDeleteRequest $request,int $id)
    {
      return  $this->calendarService->destroy($id);

    }
}
