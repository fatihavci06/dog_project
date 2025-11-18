@extends('layouts.app')

@section('title', 'User List')

@section('content')

<h1 class="h3 mb-4 text-gray-800">User List</h1>

{{-- Filter --}}
<form method="GET" action="{{ route('users') }}" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="search" class="form-control" placeholder="Search by name"
               value="{{ request('search') }}">
    </div>

    <div class="col-md-4">
        <select name="role_id" class="form-control">
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>

    <div class="col-md-2">
        <a href="{{ route('users') }}" class="btn btn-secondary w-100">Reset</a>
    </div>
</form>

{{-- Table --}}
<table class="table table-bordered table-striped" id="userTable">
    <thead class="bg-light">
        <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Pups 🐶</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
        @foreach($users as $user)
        <tr id="user-{{ $user->id }}">
            <td>{{ $user->id }}</td>

            <td>
                <strong>{{ $user->name }}</strong>
            </td>

            <td>{{ $user->email }}</td>

            <td>
                <span class="badge bg-warning text-dark">
                    {{ $user->pup_count }}
                </span>
            </td>

            {{-- STATUS BUTTON --}}
            <td class="text-center">
                <button
                    class="btn btn-sm toggle-status-btn
                        @if($user->status === 'active') btn-success
                        @elseif($user->status === 'inactive') btn-warning
                        @else btn-danger
                        @endif"
                    data-id="{{ $user->id }}"
                >
                    {{ ucfirst($user->status) }}
                </button>
            </td>

            {{-- ACTIONS --}}
            <td>

                {{-- Pup Profiles --}}
                <a href="{{ route('users.pups', $user->id) }}"
                   class="btn btn-sm btn-warning"
                   title="View Pups">
                    <i class="fas fa-dog"></i>
                </a>

                {{-- View Full Profile --}}


            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-3">
    {{ $users->links() }}
</div>

@endsection


@section('scripts')
<script>
$(document).ready(function(){

    // DataTable
    $('#userTable').DataTable({
        pageLength: 10,
        ordering: true
    });

    // Status Toggle (3-state: active, inactive, banned)
    $('.toggle-status-btn').on('click', function(){

        let userId = $(this).data('id');
        let btn = $(this);

        Swal.fire({
            title: 'Are you sure?',
            text: "This will change the user's status.",
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

                        // Change text
                        btn.text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));

                        // Reset classes
                        btn.removeClass('btn-success btn-warning btn-danger');

                        // Add color by status
                        if(newStatus === 'active'){
                            btn.addClass('btn-success');
                        }
                        else if(newStatus === 'inactive'){
                            btn.addClass('btn-warning');
                        }
                        else if(newStatus === 'banned'){
                            btn.addClass('btn-danger');
                        }

                        Swal.fire(
                            'Updated!',
                            `User status changed to ${newStatus}.`,
                            'success'
                        );
                    }
                });

            }

        });

    });

});
</script>
@endsection
