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
            // Gönderen ve hedef pup profilleri
            'my_pup_profile_id'     => 'required|exists:pup_profiles,id',
            'target_pup_profile_id' => 'required|exists:pup_profiles,id|different:my_pup_profile_id',

            // Tarih & Saat (UI ayrı gönderiyor)
            'date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today', // geçmiş tarih engeli (opsiyonel ama önerilir)
            ],

            'time' => 'required|date_format:H:i',

            // Esnek zaman
            'is_flexible' => 'boolean',

            // Konum bilgileri
            'address'   => 'nullable|string|max:255',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',

            // Opsiyonel açıklama
            'description' => 'nullable|string|max:500',
        ];
    }
}
