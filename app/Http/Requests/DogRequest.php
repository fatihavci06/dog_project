<?php

namespace App\Http\Requests;

use Faker\Provider\Base;
use Illuminate\Foundation\Http\FormRequest;

class DogRequest extends BaseRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dog_id' => 'required|integer|exists:user_dogs,id',
            'name' => 'required|string|max:255',
            'gender'=>'string|in:male,female',
            'age'=>'required|integer|min:0',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120', // Maksimum 5048 KB
            // '
            'biografy'=>'nullable|string',
            'food'=>'nullable|string|max:255',
            'health_status'=>'nullable|string|max:255',
            'size'=>'nullable|string|max:255',


            //
        ];
    }
}
