<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetOneSignalPlayerIdRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'onesignal_player_id' => 'required|string|max:255',
        ];
    }
}
