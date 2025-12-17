<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FriendSendRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'my_pup_profile_id' => 'required|integer|exists:pup_profiles,id',
            'target_pup_profile_id' => 'required|integer|exists:pup_profiles,id'

        ];
    }
}
