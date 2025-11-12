<?php

namespace App\Http\Requests;

use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;

class MobileAppStepByStepUpdateRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'id' => 'required|integer', // 'mobile_app_information' tablonuzun adını kontrol edin

            'description' => 'required|string'
        ];
    }
}
