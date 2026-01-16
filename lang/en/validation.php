<?php
return [
    'fullname_required' => 'Fullname is required.',
    'email_required'    => 'Email field is required.',
    'email_invalid'     => 'Please enter a valid email.',
    'email_unique'      => 'This email is already in use.',

    'password_required' => 'Password is required.',
    'password_min'      => 'Password must be at least :min characters.',
    'password_confirmed' => 'Password confirmation does not match.',

    'role_required'     => 'Role field is required.',
    'role_invalid'      => 'Invalid role selected.',

    'privacy_required'  => 'Privacy policy must be accepted.',
    'privacy_boolean'   => 'Invalid privacy policy value.',
    'max_images' => 'You can upload a maximum of 6 images.',
    'images_total_size' => 'The total size of the images cannot exceed 60 MB.',
    'incorrect_credentials' => 'The provided credentials are incorrect.',
    'current_password_incorrect' => 'The current password is incorrect.',
    'password_same' => 'The new password cannot be the same as the old password.',
    'feedback' => [
        'category_invalid' => 'Invalid category selected.',
        'rating_max'       => 'Rating cannot be greater than :max.',
        'image_invalid'    => 'Image must be a valid base64 encoded image.',
    ],
    'required' => 'The :attribute field is required.',
    'date_format' => 'The :attribute does not match the format :format.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'after' => 'The :attribute must be a time after :date.',
    'regex' => 'The :attribute format is invalid.',
    'string' => 'The :attribute must be a string.',
    'boolean' => 'The :attribute field must be true or false.',
    'numeric' => 'The :attribute must be a number.',
    'in' => 'The selected :attribute is invalid.',

    'attributes' => [
        'title' => 'Plan title',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'start_time' => 'Start time',
        'end_time' => 'End time',
        'color' => 'Plan color',
        'location' => 'Location',
        'lang' => 'Latitude',
        'long' => 'Longitude',
        'notes' => 'Notes',
        'icon' => 'Icon',
        'participant_id' => 'Participant',
    ],

];
