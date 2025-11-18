<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends BaseRequest
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
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
            // new_password_confirmation alanı zorunlu
        ];
    }
    public function messages()
    {
        return [
            'new_password.confirmed' => 'New password confirmation does not match.',
        ];
    }
}
