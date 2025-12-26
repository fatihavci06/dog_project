<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutgoingPendingDateRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'date_id'       => ['required', 'integer', 'exists:dates,id'],
        'my_pup_profile_id'     => 'required|exists:pup_profiles,id',
            'target_pup_profile_id' => 'required|exists:pup_profiles,id|different:my_pup_profile_id',
        'meeting_date'  => ['required', 'date'],
        'is_flexible'   => ['required', 'boolean'],
        'address'       => ['required', 'string', 'max:255'],
        'latitude'      => ['required', 'numeric', 'between:-90,90'],
        'longitude'     => ['required', 'numeric', 'between:-180,180'],
         'description'  => ['sometimes', 'nullable', 'string', 'max:1000'],
    ];
}

}
