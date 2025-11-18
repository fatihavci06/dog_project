<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SurveyUpdateRequest extends BaseRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answers' => 'required|array',

            'answers.*.question_id' => 'required|exists:questions,id',

            'answers.*.selected' => 'required|array|min:1',

            'answers.*.selected.*.option_id' => 'required|integer|exists:options,id',
            'answers.*.selected.*.order' => 'required|integer|min:1'
        ];
    }
}
