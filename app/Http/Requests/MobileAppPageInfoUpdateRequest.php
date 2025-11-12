<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MobileAppPageInfoUpdateRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'id' => ['required', 'integer', 'exists:page_infos,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image_file' => ['nullable', 'image', 'max:5120'],
        ];
    }
}
