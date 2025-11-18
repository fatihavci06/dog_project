@extends('layouts.app')

@section('title', $user->name . ' - Pup Profiles')

@section('content')

<h2 class="mb-4 text-primary">
    <i class="fas fa-dog"></i> {{ $user->name }} - Pup Profiles
</h2>

<div class="card shadow">
    <div class="card-body">

        <table id="pupTable" class="table table-bordered table-hover">
            <thead class="bg-light">
                <tr>
                    <th>#ID</th>
                    <th>Name</th>
                    <th>Breed</th>
                    <th>Age Range</th>
                    <th>Photo</th>
                    <th>Created At</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($user->pupProfiles as $pup)
                <tr>
                    <td>{{ $pup->id }}</td>
                    <td><strong>{{ $pup->name }}</strong></td>

                    <td>{{ $pup->breed->name ?? '-' }}</td>
                    <td>{{ $pup->ageRange->name ?? '-' }}</td>

                    <td class="text-center">
                        @if($pup->images->first())
                            <img src="{{ $pup->images->first()->path }}" width="60" height="60" class="rounded" style="object-fit:cover;">
                        @else
                            <i class="fas fa-image fa-2x text-muted"></i>
                        @endif
                    </td>

                    <td>{{ $pup->created_at->format('d-m-Y') }}</td>

                    <td class="text-center">
                        <a href="{{ route('pups.show', $pup->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
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
    $('#pupTable').DataTable({
        pageLength: 10,
        ordering: true,
    });
});
</script>
@endsection
