<?php

namespace App\Http\Controllers;

use App\Http\Requests\TestShowDeleteRequest;
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
    public function index($locale='en')
    {
        $data=$this->questionService->getAllQuestionsWithOptions($locale);
         return [
            'data' => $data
        ];

    }
    public function userQuestionAnswerUpdateOrCreate(UserAnswerRequest $request)
    {

         $this->questionService->userQuestionAnswerUpdateOrCreate($request->all());

    }
    public function testGet(TestShowDeleteRequest $request)
    {
        return $this->questionService->testGet($request->all());
    }


}
