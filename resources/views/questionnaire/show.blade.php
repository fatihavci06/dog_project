@extends('layouts.app')

@section('title', 'Quesetionaire Show')

@section('content')




    <div class="card shadow mb-4">
        <div class="card-body">

            <div class="card mb-3">

                <div class="card-body">
                    @foreach ($questions as $question)
                        <div class="card mb-3">
                            <div class="card-header">
                                <strong>{{ $question->question_text }}</strong>
                            </div>
                            <div class="card-body">
                                @foreach ($question->userAnswers as $answer)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="question_{{ $question->id }}"
                                            value="{{ $answer->option->id }}" checked disabled>
                                        <label class="form-check-label">
                                            {{ $answer->option->option_text }} (Rank: {{ $answer->rank }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach



                </div>
            </div>



        </div>
    </div>



@endsection
