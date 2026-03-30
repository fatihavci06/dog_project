<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\PupProfile;
use Illuminate\Validation\Validator;

class UpdateLoverLocationRequest extends BaseRequest
{
    public function rules(): array
    {
        $user = User::find($this->input('user_id'));

        $pupProfileRule = ['nullable', 'integer', 'exists:pup_profiles,id'];

        // role 4 değilse zorunlu yap
        if ($user && (int)$user->role_id !== 4) {
            $pupProfileRule[0] = 'required';
        }

        return [

            'pup_profile_id' => $pupProfileRule,

            'location'          => 'required|array',
            'location.lat'      => 'required|numeric|between:-90,90',
            'location.long'     => 'required|numeric|between:-180,180',
            'location.city'     => 'nullable|string|max:255',
            'location.district' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {

            $user = User::find($this->user_id);

            if (!$user) {
                return;
            }

            // role 4 ise ownership kontrolü yok
            if ((int)$user->role_id === 4) {
                return;
            }

            // pup_profile_id zaten required artık
            $belongsToUser = PupProfile::where('id', $this->pup_profile_id)
                ->where('user_id', $this->user_id)
                ->exists();

            if (!$belongsToUser) {
                $validator->errors()->add(
                    'pup_profile_id',
                    __('validation.pup_profile_not_belongs')
                );
            }
        });
    }
}
