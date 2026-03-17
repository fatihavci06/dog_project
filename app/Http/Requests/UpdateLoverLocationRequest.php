<?php

namespace App\Http\Requests;

class UpdateLoverLocationRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'location'      => 'required|array',
            'location.lat'  => 'required|numeric',
            'location.long' => 'required|numeric',
            'location.city' => 'nullable|string|max:255',
            'location.district' => 'nullable|string|max:255',
        ];
    }
}

