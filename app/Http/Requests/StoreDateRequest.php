<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDateRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'receiver_id' => 'required|exists:users,id',

            // UI'da tarih ve saat ayrı olduğu için ayrı validasyon yapıyoruz
            'date'        => 'required|date_format:Y-m-d', // Örn: 2024-08-15
            'time'        => 'required|date_format:H:i',   // Örn: 14:00

            'is_flexible' => 'boolean',                    // Checkbox
            'address'     => 'nullable|string|max:255',    // Manuel Adres
            'latitude'    => 'nullable|numeric',           // Harita verisi
            'longitude'   => 'nullable|numeric',           // Harita verisi
        ];
    }
}
