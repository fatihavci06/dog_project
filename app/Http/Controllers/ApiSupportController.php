<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiSupportController extends Controller
{
    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    /**
     * @param string $lang (Route'dan gelen dil parametresi)
     */
    public function index(string $lang): JsonResponse
    {
        $result = $this->supportService->getSupportByLanguage($lang);

        if (!$result) {
            return response()->json(['message' => 'Language not found or support data missing'], 404);
        }

        return response()->json($result);
    }
}
