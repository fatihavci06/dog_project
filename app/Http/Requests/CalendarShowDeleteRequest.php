<?php

namespace App\Http\Requests;

use App\Models\Calendar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalendarShowDeleteRequest extends BaseRequest
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
        $rules = [];



        return $rules;
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $id = $this->route('id');
            $calendar = Calendar::withTrashed()->find($id);
                if (!$calendar) {
                    $validator->errors()->add('id', 'Not Found.');
                } elseif ($calendar->trashed()) {
                    $validator->errors()->add('id', 'Not Found.');
                }
        });
    }
}
