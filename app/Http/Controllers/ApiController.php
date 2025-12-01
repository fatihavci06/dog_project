<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SuccessResponseResource;
use App\Http\Resources\ExceptionResponseResource;

abstract class ApiController extends BaseController
{
    /**
     * Override callAction to wrap responses and exceptions.
     *
     * @param string $method
     * @param array  $parameters
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function callAction($method, $parameters)
    {
        try {
            // 🔥 REQUEST LANGUAGE → LOCALE
            if (request()->has('language')) {
                app()->setLocale(request()->language);
            }

            $result = parent::callAction($method, $parameters);

            if ($result instanceof JsonResponse) {
                return $result;
            }

            return new SuccessResponseResource([
                'message' => __($result['message'] ?? 'messages.success'), // 🔥 sadece burası çeviri
                'data'    => $result['data'] ?? $result
            ]);
        } catch (\Exception $e) {
            return ExceptionResponseResource::fromException($e);
        }
    }
}
