<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlackListAddRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'pup_profile_id' => 'required|exists:pup_profiles,id'
        ];
    }
}
