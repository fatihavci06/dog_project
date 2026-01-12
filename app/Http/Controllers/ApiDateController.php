<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelDateRequest;
use App\Http\Requests\GetDateByIdRequest;
use App\Http\Requests\StoreDateRequest;
use App\Http\Requests\UpdateOutgoingPendingDateRequest;
use App\Services\DateService;
use Illuminate\Http\Request;

class ApiDateController extends ApiController
{
    /**
     * Yeni bir buluşma teklifi gönderir.
     */
    public function store(StoreDateRequest $request, DateService $service)
    {
        // Request'ten gelen user_id (Sender) ve form verileri
        return $service->createDate($request->user_id, $request->validated());
    }

    /**
     * Kullanıcının gönderdiği (bekleyen/onaylanan) teklifleri listeler.
     */
    public function outgoing(Request $request, DateService $service)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $pupProfileId = (int) $request->get('pup_profile_id');

        return $service->getOutgoingRequests($request->user_id, $page, $perPage, $pupProfileId);
    }

    /**
     * Kullanıcıya gelen teklifleri listeler.
     */
    public function incoming(Request $request, DateService $service)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $pupProfileId = (int) $request->get('pup_profile_id');

        return $service->getIncomingRequests($request->user_id, $page, $perPage, $pupProfileId);
    }

    /**
     * Gönderen kişi isteği iptal eder.
     * Request içinde 'date_id' gelmelidir.
     */
    public function cancel(Request $request, DateService $service)
    {
        return $service->cancelDate($request->date_id, $request->user_id);
    }

    /**
     * Alıcı kişi isteği onaylar.
     * Request içinde 'date_id' gelmelidir.
     */
    public function approve(Request $request, DateService $service)
    {
        return $service->respondDate($request->date_id, $request->user_id, 'accepted');
    }
    public function list(Request $request, DateService $service)
    {
        $page    = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);
        $pupProfileId = (int) $request->get('pup_profile_id');

        return $service->getApprovedDates($request->user_id, $page, $perPage, $pupProfileId);
    }
    public function getApprovedDateById(Request $request, DateService $service)
    {

        return $service->getApprovedDateById($request->user_id, $request->date_id);
    }

    /**
     * Alıcı kişi isteği reddeder.
     * Request içinde 'date_id' gelmelidir.
     */
    public function reject(Request $request, DateService $service)
    {
        return $service->respondDate($request->date_id, $request->user_id, 'rejected');
    }
    public function delete(CancelDateRequest $request, DateService $service)
    {
        return $service->deleteOutgoingPendingDate(
            $request->user_id,
            $request->date_id
        );
    }

    public function edit(GetDateByIdRequest $request, DateService $service)
    {
        return $service->getOutgoingPendingDateForEdit(
            $request->user_id,
            $request->date_id
        );
    }

    public function update(UpdateOutgoingPendingDateRequest $request, DateService $service)
    {
        return $service->updateOutgoingPendingDate(
            $request->user_id,
            $request->date_id,
            $request->validated()
        );
    }
}
