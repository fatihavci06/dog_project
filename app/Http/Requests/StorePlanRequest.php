<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends BaseRequest
{

protected function prepareForValidation()
    {
        // Gelen ISO tarih formatını (2025-01-15T00:00:00.000Z) parçala
        // Eğer parse edilemezse null bırak, validation hatası versin.
        try {
            $this->merge([
                'start_date' => $this->start_date ? Carbon::parse($this->start_date)->format('Y-m-d') : null,
                'end_date'   => $this->end_date ? Carbon::parse($this->end_date)->format('Y-m-d') : null,
            ]);
        } catch (\Exception $e) {
            // Tarih formatı bozuksa dokunma, validation kuralı yakalasın
        }
    }
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'color' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'], // Hex kodu kontrolü
            'location' => 'nullable|string|max:255',
            'lang' => 'nullable|numeric', // JSON'dan gelen isimle validation
            'long' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'icon' => 'nullable|in:paw,location,check,',
            'completed' => 'nullable|boolean',
            'cancelled' => 'nullable|boolean',
            'participant_id' => 'nullable|integer',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
