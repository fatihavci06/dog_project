@extends('layouts.app')

@section('title', 'User List')

@section('content')

<h1 class="h3 mb-4 text-gray-800">User List</h1>

<!-- Search + Role Filter Form -->
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

<!-- Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>#ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Email</th>
            <th>Status</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users as $user)
            <tr id="user-{{ $user->id }}">
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->role->name ?? 'No Role' }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <button class="btn btn-sm toggle-status-btn
                        {{ $user->status === 'active' ? 'btn-success' : 'btn-danger' }}"
                        data-id="{{ $user->id }}">
                        {{ ucfirst($user->status) }}
                    </button>
                </td>
                <td>{{ $user->created_at->format('d-m-Y') }}</td>
                <td>
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-user"></i> View
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No users found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination -->
<div class="mt-3">
    {{ $users->links() }}
</div>

@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-status-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        let userId = this.dataset.id;
        let button = this;

        Swal.fire({
            title: 'Are you sure?',
            text: "Change user status?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
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
                        button.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                        if(data.status === 'active'){
                            button.classList.remove('btn-danger');
                            button.classList.add('btn-success');
                        } else {
                            button.classList.remove('btn-success');
                            button.classList.add('btn-danger');
                        }

                        Swal.fire('Updated!', 'User status has been changed.', 'success');
                    }
                });
            }
        })
    });
});
</script>
@endsection
