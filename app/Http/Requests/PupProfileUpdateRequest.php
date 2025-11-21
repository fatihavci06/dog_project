<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PupProfileUpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [

            /* ---------------- BASIC FIELDS ---------------- */
            'name' => 'nullable|string|max:100',
            'sex'  => 'nullable|in:male,female,neutered',

            /* ---------------- SINGLE FK FIELDS ---------------- */
            'breed_id'         => 'nullable|exists:breads,id',
            'age_range_id'     => 'nullable|exists:age_ranges,id',
            'travel_radius_id' => 'nullable|exists:travel_radius,id',

            /* ---------------- MULTIPLE PIVOT FIELDS ---------------- */
            'looking_for_id'   => 'nullable|array',
            'looking_for_id.*' => 'integer|exists:looking_fors,id',

            'vibe_id'          => 'nullable|array',
            'vibe_id.*'        => 'integer|exists:vibes,id',

            'health_info_id'   => 'nullable|array',
            'health_info_id.*' => 'integer|exists:health_infos,id',

            'availability_for_meetup_id'   => 'nullable|array',
            'availability_for_meetup_id.*' => 'integer|exists:availability_for_meetups,id',

            /* ---------------- LOCATION ---------------- */
            'location'          => 'nullable|array',
            'location.lat'      => 'nullable|string',
            'location.long'     => 'nullable|string',
            'location.city'     => 'nullable|string|max:255',
            'location.district' => 'nullable|string|max:255',

            /* ---------------- BIOGRAPHY ---------------- */
            'biografy' => 'nullable|string',

            /* ---------------- IMAGES ---------------- */
            'images'   => 'nullable|array',
            'images.*' => [
                'nullable',
                'string',
                'regex:/^data:image\/(jpeg|jpg|png);base64,/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'looking_for_id.*.exists'   => 'Invalid looking_for_id value.',
            'vibe_id.*.exists'          => 'Invalid vibe_id value.',
            'health_info_id.*.exists'   => 'Invalid health_info_id value.',
            'availability_for_meetup_id.*.exists' => 'Invalid availability_for_meetup_id value.',

            'images.*.regex' => 'Image must be a valid Base64 encoded JPEG or PNG.',
        ];
    }
}
