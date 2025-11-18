<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PupProfileUpdateRequest  extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules():array
    {
        return [

            'name' => 'nullable|string|max:100',
            'sex'  => 'nullable|in:male,female,neutered',

            'breed_id'                   => 'nullable|exists:breads,id',
            'age_range_id'               => 'nullable|exists:age_ranges,id',
            'looking_for_id'             => 'nullable|exists:looking_fors,id',
            'vibe_id'                    => 'nullable|exists:vibes,id',
            'health_info_id'             => 'nullable|exists:health_infos,id',
            'travel_radius_id'           => 'nullable|exists:travel_radius,id',
            'availability_for_meetup_id' => 'nullable|exists:availability_for_meetups,id',

            'location'          => 'nullable|array',
            'location.lat'      => 'nullable|string',
            'location.long'     => 'nullable|string',
            'location.city'     => 'nullable|string',
            'location.district' => 'nullable|string',

            'biografy' => 'nullable|string',

            'images'   => 'nullable|array',
            'images.*' => ['nullable','string','regex:/^data:image\/(jpeg|jpg|png);base64,/'],
        ];
    }

    public function messages()
    {
        return [
            'images.*.regex' => 'Image must be a valid base64 encoded JPEG or PNG.',
        ];
    }


}
