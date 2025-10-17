<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetOneSignalPlayerIdRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ApiNotificationController extends ApiController
{
    //
    protected  $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function setOneSignalPlayerId(SetOneSignalPlayerIdRequest $request)
    {

        return $this->notificationService->setOneSignalPlayerId($request->all());
    }
}
