<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\JsonResponse;
use Throwable;

class ExceptionResponseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'success' => false,
            'message' => $this->resource['message'] ?? 'Bir hata oluştu',
            'errors' => $this->resource['errors'] ?? null
        ];
    }

    public static function fromException(Throwable $e): JsonResponse
    {
        $statusCode = ($e->getCode() >= 100 && $e->getCode() < 600) ? $e->getCode() : 500;

        return (new self([
            'message' => $e->getMessage(),
            'errors' => method_exists($e, 'errors') ? $e->errors() : null,
            'code' => $statusCode
        ]))->response()->setStatusCode($statusCode);
    }
}
