<?php

namespace App\Http\Requests;

use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;

class CalendarRequest extends BaseRequest
{



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'event_date' => 'required|date_format:Y-m-d H:i',
            'user_id' => 'required|exists:users,id',
        ];

        // PUT veya PATCH isteğinde id zorunlu olsun
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $id = $this->route('id'); // URL'deki id
            if ($id) {
                // id varsa, sadece o id'nin varlığını doğrula
                $rules['id'] = 'exists:calendars,id';
            }
        }


        return $rules;
    }
}
