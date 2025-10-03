<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Option;
use App\Models\TestUserRole;
use Illuminate\Validation\Validator;

class UserAnswerRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        // Route parametresindeki dog_id'yi request'e ekle
        if ($this->routeIs('test.update')) {
            if ($this->route('test_id')) {
                $this->merge([
                    'test_id' => $this->route('test_id'),
                ]);
            }
        }
    }
    public function rules(): array
    {
        $rules = [
            'role_id' => 'required|in:3,4',
            'user_dogs' => 'required_if:role_id,3|array|min:1',
            'user_dogs.*.name' => 'required_if:role_id,3|string|max:255',
            'user_dogs.*.gender' => 'required_if:role_id,3|string|in:male,female',
            'user_dogs.*.age' => 'required_if:role_id,3|integer|min:0',
            'user_dogs.*.photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'user_dogs.*.biography' => 'nullable|string',
            'user_dogs.*.food' => 'nullable|string|max:255',
            'user_dogs.*.health_status' => 'nullable|string|max:255',
            'user_dogs.*.size' => 'nullable|string|max:255',
            'answers' => [
                'required',
                'array',
                'size:5', // 5 soru zorunlu
            ],
            'answers.*.question_id' => [
                'required',
                'exists:questions,id',
            ],
            'answers.*.options' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $parts = explode('.', $attribute); // answers.0.options
                    $questionIndex = $parts[1];

                    // Soruların seçenek sayısını kontrol et
                    if ($questionIndex == 0 && count($value) !== 9) {
                        $fail("Question 1 must have exactly 9 options.");
                    } elseif (in_array($questionIndex, [1, 2, 3, 4]) && count($value) !== 4) {
                        $fail("Question " . ($questionIndex + 1) . " must have exactly 4 options.");
                    }
                }
            ],
            'answers.*.options.*.option_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $parts = explode('.', $attribute); // answers.0.options.1.option_id
                    $questionIndex = $parts[1];
                    $questionId = $this->input("answers.$questionIndex.question_id");

                    $exists = Option::where('id', $value)
                        ->where('question_id', $questionId)
                        ->exists();

                    if (!$exists) {
                        $fail("The selected option_id {$value} is invalid for question_id {$questionId}.");
                    }
                },
            ],
            'answers.*.options.*.rank' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $parts = explode('.', $attribute); // answers.0.options.1.rank
                    $questionIndex = $parts[1];

                    $options = collect($this->input("answers.$questionIndex.options"));
                    $ranks = $options->pluck('rank');

                    // rank unique olmalı
                    if ($ranks->count() !== $ranks->unique()->count()) {
                        $fail("Duplicate rank values in question " . ($questionIndex + 1) . ".");
                    }

                    // rank aralığı 1…n (n = o sorudaki option sayısı)
                    $maxRank = count($options);
                    if ($value < 1 || $value > $maxRank) {
                        $fail("Rank must be between 1 and {$maxRank} for question " . ($questionIndex + 1) . ".");
                    }
                },
            ],
        ];

        if ($this->routeIs('test.update')) {
            $rules['test_id'] = 'required|integer|exists:test_user_roles,id';
        }
        return $rules;
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Role ID is required.',
            'role_id.in' => 'Role ID must be either 3 or 4.',
            'answers.required' => 'Answers are required.',
            'answers.size' => 'You must answer all 5 questions.',
            'answers.*.question_id.required' => 'Question ID is required.',
            'answers.*.options.required' => 'Options are required for each question.',
            'answers.*.options.*.option_id.required' => 'Option ID is required.',
            'answers.*.options.*.rank.required' => 'Rank is required for each option.',
            'user_dogs.required_if' => 'Dog information is required when role_id is 3.',
            'user_dogs.*.name.required_if' => 'Dog name is required when role_id is 3.',
            'user_dogs.*.gender.required_if' => 'Dog gender is required when role_id is 3.',
            'user_dogs.*.age.required_if' => 'Dog age is required when role_id is 3.',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $userId = $this->input('user_id');
            if ($this->routeIs('test.update')) {
                $testId = $this->input('test_id');
                $test = TestUserRole::where('id', $testId)
                    ->where('user_id', $userId)
                    ->first();

                if (!$test) {
                    $validator->errors()->add('test_id', 'Test not found for this user.');
                }
            }
        });
    }
}
