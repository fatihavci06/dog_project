<?php

namespace App\Http\Controllers;

use App\Models\PupProfile;
use Illuminate\Http\Request;

class PupProfileController extends Controller
{
    public function surveyDetail(PupProfile $pup, $questionId)
    {
        $pup->load([
            'answers.option',
            'answers.question'
        ]);

        $answers = $pup->answers->where('question_id', $questionId)->sortBy('order_index');

        return view('pups.survey_detail', compact('pup', 'answers'));
    }
    public function show(PupProfile $pup)
    {
        $pup->load([
            'images',
            'breed',
            'ageRange',
            'lookingFor',
            'vibe',
            'healthInfo',
            'travelRadius',
            'availabilityForMeetup',
            'answers.option',
            'answers.question'
        ]);

        return view('pups.show', compact('pup'));
    }

}
