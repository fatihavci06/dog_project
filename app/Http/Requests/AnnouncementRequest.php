<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // You can control access via policy if needed
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'role_id' => 'nullable|exists:roles,id',
        ];

        // Example: if you want different rules for update
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = 'required|string|max:255';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a valid string.',
            'title.max' => 'The title may not be greater than 255 characters.',

            'content.required' => 'The content field is required.',
            'content.string' => 'The content must be a valid string.',

            'role_id.exists' => 'The selected role is invalid.',
        ];
    }
}
