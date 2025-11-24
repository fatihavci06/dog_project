<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuccessResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $resource = $this->resource;

        $key = $resource['message'] ?? null;

        return [
            'success' => true,
            'message' => $key ? __($key) : 'Transaction successful',
            'data'    => $resource['data'] ?? null,
        ];
    }
}
