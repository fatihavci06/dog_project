@extends('layouts.app')

@section('title', 'Reported Profiles')

@section('content')

<h1 class="h3 mb-4 text-gray-800">Reported Profiles</h1>

@if(session('success'))
    <div class="alert alert-success border-left-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Flags List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="flagsTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Reporter</th>
                        <th>Flagged Profile (Dog)</th>
                        <th>Reason</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($flags as $flag)
                        @php
                            $reason = 'Unknown';
                            if ($flag->flag_type == 1) $reason = 'Spam';
                            if ($flag->flag_type == 2) $reason = 'Abuse';
                            if ($flag->flag_type == 3) $reason = 'Fake Account';
                            if ($flag->flag_type == 4) $reason = 'Inappropriate Content';
                        @endphp
                        <tr>
                            <td>{{ $flag->id }}</td>
                            <td>
                                @if($flag->reporter)
                                    <strong>{{ $flag->reporter->name }}</strong>
                                @else
                                    <span class="text-muted">Deleted User</span>
                                @endif
                            </td>
                            <td>
                                @if($flag->flaggedProfile)
                                    <a href="{{ route('users.pups', ['user' => $flag->flaggedProfile->user->id ?? 0]) }}" class="text-primary font-weight-bold">
                                        {{ $flag->flaggedProfile->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Deleted Profile</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-warning text-dark px-2 py-1">
                                    {{ $reason }}
                                </span>
                            </td>
                            <td>{{ $flag->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-center">
                                {{-- Toggle User Status --}}
                                @if($flag->flaggedProfile && $flag->flaggedProfile->user)
                                <button class="btn btn-sm toggle-status-btn 
                                    @if($flag->flaggedProfile->user->status === 'active') btn-warning 
                                    @else btn-success 
                                    @endif" 
                                    data-id="{{ $flag->flaggedProfile->user->id }}" title="Toggle User Status">
                                    @if($flag->flaggedProfile->user->status === 'active')
                                        <i class="fas fa-ban"></i> Suspend
                                    @else
                                        <i class="fas fa-check"></i> Activate
                                    @endif
                                </button>
                                @endif

                                {{-- Dismiss Flag --}}
                                <form method="POST" action="{{ route('profile-flags.destroy', $flag->id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger delete-flag-btn" title="Dismiss Flag">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Basic Datatable
    $('#flagsTable').DataTable({
        pageLength: 20,
        ordering: true,
        order: [[0, "desc"]]
    });

    // Dismiss flag confirmation
    $('.delete-flag-btn').on('click', function(e) {
        e.preventDefault();
        let form = $(this).closest('form');
        Swal.fire({
            title: 'Delete flag?',
            text: "Are you sure you want to dismiss this flag? The report will be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Yes, dismiss it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Status Toggle (From users.blade.php)
    $('.toggle-status-btn').on('click', function(){
        let userId = $(this).data('id');
        let btn = $(this);

        Swal.fire({
            title: 'Change User Status?',
            text: "This will suspend or reactivate the user.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(result.isConfirmed){
                fetch(`/users/${userId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success){
                        let newStatus = data.status;
                        
                        btn.removeClass('btn-success btn-warning btn-danger');
                        if(newStatus === 'active'){
                            btn.addClass('btn-warning');
                            btn.html('<i class="fas fa-ban"></i> Suspend');
                        } else {
                            btn.addClass('btn-success');
                            btn.html('<i class="fas fa-check"></i> Activate');
                        }
                        
                        Swal.fire('Updated!', `User has been successfully updated.`, 'success');
                    }
                });
            }
        });
    });
});
</script>
@endsection
