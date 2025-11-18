<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RegisterRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
                Rule::in(['male', 'female','neutered'])
            ],

            /* ------------------------------ OPTIONAL FK -------------------------------- */

            'pup_profile.breed_id' => 'nullable|exists:breads,id',
            'pup_profile.age_range_id' => 'nullable|exists:age_ranges,id',
            'pup_profile.looking_for_id' => 'nullable|exists:looking_fors,id',
            'pup_profile.vibe_id' => 'nullable|exists:vibes,id',
            'pup_profile.health_info_id' => 'nullable|exists:health_infos,id',
            'pup_profile.travel_radius_id' => 'nullable|exists:travel_radius,id',
            'pup_profile.availability_for_meetup_id' => 'nullable|exists:availability_for_meetups,id',

            /* --------------------------- LOCATION OPTIONAL --------------------------- */

            'pup_profile.location' => 'nullable|array',

            'pup_profile.location.lat'     => 'nullable|string',
            'pup_profile.location.long'    => 'nullable|string',
            'pup_profile.location.city'    => 'nullable|string|max:255',
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

            /* ------------------------- IMAGES (BASE64 REQUIRED) ------------------------ */

            'pup_profile.images' => 'nullable|array',

            'pup_profile.images.*' => [
                'required',
                'string',
                'regex:/^data:image\/(jpeg|jpg|png);base64,/',
            ],
        ];
    }

    public function messages()
    {
        return [
            'pup_profile.answers.*.ordered_option_ids.*.exists' =>
            'Option ID question ile eşleşmiyor veya options tablosunda bulunamadı.',

            'pup_profile.images.*.regex' =>
            'Images base64 formatında olmalıdır (data:image/jpeg;base64,...)',
        ];
    }
}
