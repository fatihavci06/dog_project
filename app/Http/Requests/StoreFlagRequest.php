<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlagRequest extends BaseRequest
{


    public function rules(): array
    {
        return [
            'flagged_profile_id' => 'required|exists:pup_profiles,id',
        ];
    }
}
