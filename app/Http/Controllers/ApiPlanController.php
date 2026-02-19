<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlanRequest;
use App\Models\Plan;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\In;

class ApiPlanController extends ApiController
{
    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Listeleme (GET)
     */
    public function index(Request $request)
    {
       return $this->planService->getAllPlans($request->user_id);


    }

    /**
     * Oluşturma (POST)
     */
    public function store(StorePlanRequest $request)
    {
        return $this->planService->createPlan($request->validated());


    }

    /**
     * Detay Görüntüleme (GET)
     */
    public function show(int $id)
    {
       return Plan::findOrFail($id);


    }

    /**
     * Güncelleme (PUT/PATCH)
     */
    public function update(StorePlanRequest $request,int $id)
    {
        $plan = Plan::findOrFail($id);

        // Policy kontrolü: $this->authorize('update', $plan);

        return $this->planService->updatePlan($plan, $request->validated());


    }

    /**
     * Silme (DELETE)
     */
    public function destroy(int $id)
    {
        // Policy kontrolü: $this->authorize('delete', $plan);

      return  $this->planService->deletePlan(Plan::findOrFail($id));

    }
    public function upcoming(Request $request)
    {
        // Policy kontrolü: $this->authorize('delete', $plan);

       return $this->planService->getUpcomingPlans($request->user_id);

    }
}
