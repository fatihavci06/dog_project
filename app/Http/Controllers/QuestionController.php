<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAnswerRequest;
use App\Models\Question;
use App\Models\User;
use App\Models\UserAnswer;
use App\Services\QuestionService;
use Illuminate\Http\Request;

class QuestionController extends ApiController
{
    protected $questionService;
    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }
    public function index()
    {
        return $this->questionService->getAllQuestionsWithOptions();
    }
    public function userQuestionAnswerUpdateOrCreate(UserAnswerRequest $request)
    {

        return $this->questionService->userQuestionAnswerUpdateOrCreate($request->all());
    }

}
