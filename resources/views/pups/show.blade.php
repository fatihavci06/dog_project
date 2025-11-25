@extends('layouts.app')

@section('title', $pup->name . ' Profile')

@section('content')

    <h2 class="mb-4 text-primary">
        <i class="fas fa-dog"></i> {{ $pup->name }} - Profile Detail
    </h2>

    <div class="row">

        {{-- SOL TARAF: Temel Bilgiler + İlişkiler --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="m-0">Basic Info</h5>
                </div>

                <div class="card-body">

                    <p><strong>Name:</strong> {{ $pup->name ?? '-' }}</p>
                    <p><strong>Sex:</strong> {{ $pup->sex ? ucfirst($pup->sex) : '-' }}</p>

                     <p><strong>Breed:</strong> {{ $pup->breed->name ?? '-' }}</p>
                    <p><strong>Age Range:</strong> {{ $pup->ageRange->name ?? '-' }}</p>

                    <p><strong>Looking For:</strong>
                        {{ $pup->lookingFor->pluck('name')->join(', ') ?: '-' }}
                    </p>

                    <p><strong>Vibe:</strong>
                        {{ $pup->vibe->pluck('name')->join(', ') ?: '-' }}
                    </p>

                    <p><strong>Health Info:</strong>
                        {{ $pup->healthInfo->pluck('name')->join(', ') ?: '-' }}
                    </p>

                     <p><strong>Travel Radius:</strong> {{ $pup->travelRadius->name ?? '-' }}</p>

                    <p><strong>Availability for Meetup:</strong>
                        {{ $pup->availabilityForMeetup->pluck('name')->join(', ') ?: '-' }}
                    </p>


                    <hr>

                    <h6 class="text-info mb-2"><i class="fas fa-map-marker-alt"></i> Location</h6>
                    <p><strong>City:</strong> {{ $pup->city ?? '-' }}</p>
                    <p><strong>District:</strong> {{ $pup->district ?? '-' }}</p>
                    <p><strong>Lat / Long:</strong>
                        {{ $pup->lat ?? '-' }} / {{ $pup->long ?? '-' }}
                    </p>

                    <hr>

                    <h6 class="text-info mb-2"><i class="fas fa-book-open"></i> Biography</h6>
                    <p class="border rounded p-3 bg-light">
                        {{ $pup->biography ?? 'No biography provided.' }}
                    </p>

                </div>
            </div>
        </div>

        {{-- SAĞ TARAF: Fotoğraflar --}}
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="m-0">Images</h5>
                </div>
                <div class="card-body">
                    @if ($pup->images->isEmpty())
                        <p class="text-muted">No images uploaded.</p>
                    @else
                        <div class="d-flex flex-wrap">
                            @foreach ($pup->images as $img)
                                <img src="{{ $img->path }}" width="100" height="100" class="rounded m-2"
                                    style="object-fit:cover;">
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>


    {{-- Survey section --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="m-0">Survey Answers</h5>
        </div>

        <div class="card-body">
            @if ($pup->answers->isEmpty())
                <p class="text-muted">No survey answers for this pup.</p>
            @else
                @foreach ($pup->answers->groupBy('question_id') as $qid => $answers)
                    <div class="border p-3 rounded mb-3">
                        <strong>{{ $answers->first()->question->question_text }}</strong>
                        <ol class="mt-2">
                            @foreach ($answers->sortBy('order_index') as $ans)
                                <li>{{ $ans->option->option_text }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <a href="{{ route('users.pups', $pup->user_id) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Pup List
    </a>

@endsection
