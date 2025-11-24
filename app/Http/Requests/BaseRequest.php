<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    protected function prepareForValidation()
    {
        if ($this->has('language')) {
            app()->setLocale($this->language);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        // ÇEVRİ: tüm validation mesajlarını __() ile çeviriyoruz
        $translated = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            foreach ($messages as $msg) {
                $translated[$field][] = __($msg);
            }
        }

        // İlk mesajı üst "message" alanına koyuyoruz
        $firstMessage = collect($translated)->first()[0];

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $firstMessage,
            'errors'  => $translated,
        ], 422));
    }
}
