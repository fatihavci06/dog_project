<?php

namespace App\Http\Requests;


class ProfileUpdateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',

            'location_city' => 'nullable|string|max:100',
            'location_district' => 'nullable|string|max:100',
            'biography' => 'nullable|string',
            'photo' => 'nullable|image|max:5048', // Maksimum 5048 KB
        ];

        // Eğer method PUT veya PATCH ise update işlemi olarak varsay


        return $rules;
    }
}
