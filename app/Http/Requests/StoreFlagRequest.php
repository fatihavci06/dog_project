<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlagRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'flagged_profile_id' => 'required|exists:pup_profiles,id',
            'flag_type' => 'required|integer|in:1,2,3,4',
        ];
    }
}
