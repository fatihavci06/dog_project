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
            // Call the framework's callAction, not self::
            $result = parent::callAction($method, $parameters);

            // If a JsonResponse or Resource is returned, pass through
            if ($result instanceof JsonResponse) {
                return $result;
            }


            // Otherwise wrap in SuccessResponseResource
            return new SuccessResponseResource([
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            // Wrap exceptions in your ExceptionResponseResource
            return ExceptionResponseResource::fromException($e);
        }
    }
}
