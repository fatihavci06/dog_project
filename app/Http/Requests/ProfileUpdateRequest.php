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
        return [

            /* Tek alan: fullname */
            'fullname' => 'required|string|max:150',

            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'country' => 'nullable|string|max:120',
            'biography' => 'nullable|string|max:1200',
            'travel_radius' => 'nullable|integer|exists:travel_radius,id',


            'photo' => [
                'nullable',
                'string',
                'regex:/^data:image\/(jpeg|jpg|png);base64,/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'photo.regex' => 'Profile photo must be a valid base64 image.'
        ];
    }
}
