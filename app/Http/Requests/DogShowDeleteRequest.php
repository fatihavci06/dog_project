<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\UserDog;
use Faker\Provider\Base;

class DogShowDeleteRequest extends BaseRequest
{
    protected function prepareForValidation()
    {
        // Route parametresindeki dog_id'yi request'e ekle
        if ($this->route('dog_id')) {
            $this->merge([
                'dog_id' => $this->route('dog_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'dog_id' => 'required|integer|exists:user_dogs,id',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $dogId = $this->input('dog_id');
            $userId = $this->input('user_id');

            $dog = UserDog::where('id', $dogId)
                          ->where('user_id', $userId)
                          ->first();

            if (!$dog) {
                $validator->errors()->add('dog_id', 'Dog not found for this user.');
            }
        });
    }
}
