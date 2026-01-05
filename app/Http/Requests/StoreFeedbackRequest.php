<?php

namespace App\Http\Requests;

use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedbackRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'category' => [
                'required',
                Rule::in(['bug', 'öneri', 'içerik', 'şikayet', 'diğer']),
            ],

            'subject' => 'required|string|max:255',
            'message' => 'required|string',

            'rating'   => 'nullable|integer|min:1|max:5',
            'priority' => 'nullable|in:low,medium,high',

            // 🔥 BASE64 IMAGE
            'image' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!preg_match('/^data:image\/(png|jpg|jpeg|webp);base64,/', $value)) {
                        $fail(__('validation.feedback.image_invalid'));
                    }
                },
            ],

            'contact.allow_contact' => 'nullable|boolean',
            'contact.full_name'     => 'nullable|string|max:255',
            'contact.email'         => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => __('validation.feedback.category_invalid'),
            'rating.max'  => __('validation.feedback.rating_max'),
        ];
    }
}
