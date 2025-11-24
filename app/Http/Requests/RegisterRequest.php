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

            'answers' => 'nullable|array',

            'answers.*.question_id' => [
                'required',
                'integer',
                'exists:questions,id'
            ],

            'answers.*.ordered_option_ids' => 'required|array|min:1',

            'answers.*.ordered_option_ids.*' => [
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
    public function messages()
    {
        return [
            'fullname.required' => 'validation.fullname_required',
            'email.required'    => 'validation.email_required',
            'email.email'       => 'validation.email_invalid',
            'email.unique'      => 'validation.email_unique',
            'password.required' => 'validation.password_required',
            'password.min'      => 'validation.password_min',
            'password.confirmed' => 'validation.password_confirmed',

            'role.required'     => 'validation.role_required',
            'role.in'           => 'validation.role_invalid',

            'privacy_policy.required' => 'validation.privacy_required',
            'privacy_policy.boolean'  => 'validation.privacy_boolean',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $images = $this->input('pup_profile.images');

            if (!$images || !is_array($images)) {
                return;
            }

            // 6 adet max
            if (count($images) > 6) {
                $validator->errors()->add('pup_profile.images', __('validation.max_images'));
            }

            // Toplam boyut hesaplama
            $totalBytes = 0;

            foreach ($images as $img) {
                // header'dan sonrasını al (base64 kısmı)
                $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $img);

                // Base64'i byte'a çevir
                $bytes = (int) (strlen($base64) * 0.75);

                $totalBytes += $bytes;
            }

            // 60 MB = 60 * 1024 * 1024
            if ($totalBytes > 60 * 1024 * 1024) {
                $validator->errors()->add('pup_profile.images', __('validation.images_total_size'));
            }
        });
    }
}
