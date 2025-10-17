@extends('layouts.app')

@section('title', 'Send Notification')

@section('content')

    <h2 class="mb-4">Send Notification 📩</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
 <div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
    <form action="{{ route('notifications.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Enter notification title..." required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Message</label>
            <textarea name="message" class="form-control" rows="4" placeholder="Enter message content..." required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">URL (Optional)</label>
            <input type="url" name="url" class="form-control" placeholder="https://example.com">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Select Users</label>
            <select name="user_ids[]" id="userSelect" class="form-select" multiple>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <div class="form-text">Search and select specific users to send notifications.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Select Roles</label>
            <select name="role_ids[]" id="roleSelect" class="form-select" multiple>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <div class="form-text">You can also target users by their role.</div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane me-1"></i> Send Notification
        </button>
    </form>
 </div> </div> </div>
@endsection

@section('scripts')
<!-- Select2 (User search) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize searchable multi-selects
        $('#userSelect').select2({
            placeholder: "Search and select users...",
            allowClear: true,
            width: '100%'
        });

        $('#roleSelect').select2({
            placeholder: "Select roles...",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
