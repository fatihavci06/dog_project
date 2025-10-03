<?php

namespace App\Http\Requests;

use App\Models\TestUserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TestShowDeleteRequest extends BaseRequest
{


    protected function prepareForValidation()
    {
        // Route parametresindeki dog_id'yi request'e ekle
        if ($this->route('test_id')) {
            $this->merge([
                'test_id' => $this->route('test_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'test_id' => 'required|integer|exists:test_user_roles,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $testId = $this->input('test_id');
            $userId = $this->input('user_id');

            $dog = TestUserRole::where('id', $testId)
                          ->where('user_id', $userId)
                          ->first();

            if (!$dog) {
                $validator->errors()->add('test_id', 'Test not found for this user.');
            }
        });
    }
}
