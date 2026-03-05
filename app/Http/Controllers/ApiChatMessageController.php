<?php

namespace App\Http\Controllers;

use App\Http\Controllers\ApiController; // Senin yapında bu var olarak varsayıyorum
use App\Services\MobileAppChatMessageService;
use Illuminate\Http\Request;

class ApiChatMessageController extends ApiController
{
    protected MobileAppChatMessageService $service;

    public function __construct(MobileAppChatMessageService $service)
    {
        $this->service = $service;
    }

    /**
     * Mobil uygulama için hazır chat mesajlarını listele (GET)
     */
    public function index(Request $request)
    {

        $locale = app()->getLocale();

        // 2. Servisten formatlanmış veriyi al
        return $this->service->getSuggestionsForApi($locale);
    }
}
