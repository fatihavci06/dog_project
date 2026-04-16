<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PupProfileCreateRequest extends BaseRequest
{

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->has('images')) {
            $images = $this->images;

            if (!is_array($images)) {
                $trimmed = trim((string)$images);
                if ($trimmed === '' || $trimmed === 'null' || $trimmed === 'undefined') {
                    $this->merge(['images' => null]);
                    return;
                }
                $images = [$images];
            }

            $filtered = array_filter((array)$images, function($img) {
                if (is_null($img)) return false;
                $s = trim((string)$img);
                return $s !== '' && $s !== 'null' && $s !== 'undefined';
            });

            $this->merge([
                'images' => !empty($filtered) ? array_values($filtered) : null
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules():array
    {
        return [

            /* -------------------------------------------
               BASIC INFO
            ------------------------------------------- */
            'name'   => 'required|string|max:100',
            'sex'    => 'nullable|in:male,female,neutered',

            /* -------------------------------------------
               FOREIGN KEYS (nullable)
            ------------------------------------------- */
            'breed_id'                   => 'nullable|exists:breads,id',
            'age_range_id'               => 'nullable|exists:age_ranges,id',
            'looking_for_id'             => 'nullable|exists:looking_fors,id',
            'vibe_id'                    => 'nullable|exists:vibes,id',
            'health_info_id'             => 'nullable|exists:health_infos,id',
            'travel_radius_id'           => 'nullable|exists:travel_radius,id',
            'availability_for_meetup_id' => 'nullable|exists:availability_for_meetups,id',

            /* -------------------------------------------
               LOCATION (optional)
            ------------------------------------------- */
            'location'            => 'nullable|array',
            'location.lat'        => 'nullable|string',
            'location.long'       => 'nullable|string',
            'location.city'       => 'nullable|string',
            'location.district'   => 'nullable|string',

            /* -------------------------------------------
               BIOGRAPHY
            ------------------------------------------- */
            'biografy' => 'required|string',

            /* -------------------------------------------
               IMAGES (base64)
            ------------------------------------------- */
             'images'     => 'nullable|array',
            'images.*'   => ['nullable','string', 'regex:/^data:image\/(jpeg|jpg|png);base64,/'],
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
            /* -------------------------------------------
               SURVEY ANSWERS (optional)
            ------------------------------------------- */

        ];
    }

    public function messages()
    {
        return [
            'images.*.regex' => 'Image must be a valid base64 encoded JPEG or PNG.',
        ];
    }


}
