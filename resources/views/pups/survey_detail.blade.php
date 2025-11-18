@extends('layouts.app')

@section('title', 'Survey Detail')

@section('content')

<h2 class="text-primary mb-3">
    <i class="fas fa-list-ol"></i> {{ $pup->name }} - Survey Detail
</h2>

<div class="card shadow">
    <div class="card-body">

        <strong>{{ $answers->first()->question->question_text }}</strong>

        <ol class="mt-3">
            @foreach($answers as $ans)
                <li>{{ $ans->option->option_text }}</li>
            @endforeach
        </ol>

    </div>
</div>

<a href="{{ route('pups.show', $pup->id) }}" class="btn btn-secondary mt-3">
    <i class="fas fa-arrow-left"></i> Back
</a>

@endsection
