<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RegisterRequest extends BaseRequest
{
    /**
     * Validation Rules
     */
    public function rules(): array
    {

        return [

            /* ----------------------------- USER FIELDS ----------------------------- */

            'fullname'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|confirmed|min:8',

            'role' => [
                'required',
                Rule::in([3, 4]) // 3: dog owner, 4: dog lover
            ],

            'privacy_policy' => 'required|boolean',
            'newlestter'     => 'nullable|boolean',

            /* ---------------------------- PUP PROFILE ROOT ---------------------------- */

            'pup_profile' => 'nullable|array',

            'pup_profile.name' => 'nullable|string|max:255',

            'pup_profile.sex'  => [
                'nullable',
                Rule::in(['male', 'female', 'neutered'])
            ],

            /* ------------------------------ SINGLE SELECT ------------------------------ */

            'pup_profile.breed_id'          => 'nullable|integer|exists:breads,id',
            'pup_profile.age_range_id'      => 'nullable|integer|exists:age_ranges,id',
            'pup_profile.travel_radius_id'  => 'nullable|integer|exists:travel_radius,id',

            /* ------------------------------ MULTI SELECT ------------------------------ */

            'pup_profile.looking_for_id'   => 'nullable|array|min:1',
            'pup_profile.looking_for_id.*' => 'integer|distinct|exists:looking_fors,id',

            'pup_profile.vibe_id'   => 'nullable|array|min:1',
            'pup_profile.vibe_id.*' => 'integer|distinct|exists:vibes,id',

            'pup_profile.health_info_id'   => 'nullable|array|min:1',
            'pup_profile.health_info_id.*' => 'integer|distinct|exists:health_infos,id',

            'pup_profile.availability_for_meetup_id'   => 'nullable|array|min:1',
            'pup_profile.availability_for_meetup_id.*' => 'integer|distinct|exists:availability_for_meetups,id',

            /* --------------------------- LOCATION OPTIONAL --------------------------- */

            'pup_profile.location' => 'nullable|array',

            'pup_profile.location.lat'      => 'nullable|string',
            'pup_profile.location.long'     => 'nullable|string',
            'pup_profile.location.city'     => 'nullable|string|max:255',
            'pup_profile.location.district' => 'nullable|string|max:255',

            /* ------------------------------- BIOGRAPHY -------------------------------- */

            'pup_profile.biografy' => 'nullable|string',

            /* ------------------------------- ANSWERS --------------------------------- */

            'pup_profile.answers' => 'nullable|array',

            'pup_profile.answers.*.question_id' => [
                'required',
                'integer',
                'exists:questions,id'
            ],

            'pup_profile.answers.*.ordered_option_ids' => 'required|array|min:1',

            'pup_profile.answers.*.ordered_option_ids.*' => [
                'integer',
                'exists:options,id'
            ],

            /* ------------------------- IMAGES (BASE64) ------------------------ */

            'pup_profile.images' => 'nullable|array',

            'pup_profile.images.*' => [
                'required',
                'string',
                'regex:/^data:image\/(jpeg|jpg|png);base64,/',
            ],
        ];
    }



}
