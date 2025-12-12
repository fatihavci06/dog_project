@extends('layouts.app')

@section('title', $user->name . ' - Survey List')

@section('content')

<h2 class="mb-4 text-danger">
    <i class="fas fa-list-ol"></i> {{ $user->name }} - Survey Responses
</h2>

<div class="card shadow">
    <div class="card-body">


        <table id="surveyTable" class="table table-bordered table-striped">
            <thead class="bg-light">
                <tr>
                    <th>Pup</th>
                    <th>Question</th>
                    <th>Answer Count</th>
                    <th>Updated At</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>

                @foreach($user->pupProfiles as $pup)

                    @foreach($pup->answers->groupBy('question_id') as $questionId => $answers)
                    <tr>
                        <td>{{ $pup->name }}</td>

                        <td>{{ $answers->first()->question->question_text }}</td>

                        <td>
                            <span class="badge bg-info text-white">{{ $answers->count() }}</span>
                        </td>

                        <td>{{ $answers->first()->updated_at->format('d-m-Y H:i') }}</td>

                        <td class="text-center">
                            <a href="{{ route('pups.survey.show', [$pup->id, $questionId]) }}"
                                class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach

                @endforeach

            </tbody>

        </table>

    </div>
</div>

<a href="{{ route('users') }}" class="btn btn-secondary mt-3">
    <i class="fas fa-arrow-left"></i> Back to User List
</a>

@endsection

@section('scripts')
<script>
$(document).ready(function(){
    $('#surveyTable').DataTable({
        pageLength: 10,
        ordering: true
    });
});
</script>
@endsection
