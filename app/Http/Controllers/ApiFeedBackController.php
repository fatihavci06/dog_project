<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeedbackRequest;
use App\Services\FeedbackService;
use Illuminate\Http\Request;

class ApiFeedBackController extends Controller
{
    public function __construct(
        protected FeedbackService $service
    ) {}

    public function store(StoreFeedbackRequest $request)
    {
        $feedback = $this->service->create(
            $request->validated(),
            $request->user_id // middleware veya auth'tan geliyor
        );

        return response()->json([
            'success' => true,
            'message' => 'Geri bildiriminiz başarıyla alındı.',
            'data' => [
                'id'       => $feedback->id,
                'category' => $feedback->category,
                'subject'  => $feedback->subject,
                'created_at' => $feedback->created_at,
            ]
        ], 201);
    }
    public function index(Request $request)
    {
        $result = $this->service->listUserFeedbacks(
            userId: $request->user_id,
            page: (int) $request->get('page', 1),
            perPage: (int) $request->get('per_page', 10),
        );

        return response()->json($result);
    }
}
