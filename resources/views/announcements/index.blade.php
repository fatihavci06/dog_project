@extends('layouts.app')

@section('title', 'Announcement Management')

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Laravel'in form doğrulama (validation) hataları için: --}}
@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Dikkat!</strong> Lütfen aşağıdaki hataları düzeltin.
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 text-gray-800">Announcement Management 📢</h1>
    <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal"
            data-bs-target="#announcementModal" onclick="resetModal()">
        <i class="fas fa-plus fa-sm text-white-50"></i> Create New Announcement
    </button>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('announcements.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by Title..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role_id" class="form-control">
                        <option value="">All Roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
                @if (request()->has('search') || request('role_id'))
                    <div class="col-md-2">
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                @endif
            </div>
        </form>
 card   </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Announcements ({{ $announcements->total() ?? count($announcements) }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" width="100%" cellspacing="0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Title</th>
                        <th>Content (Snippet)</th>
                        <th>Target Role</th>
                        <th>Starts At</th>
                        <th>Ends At</th>
                        <th>Created Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($announcements as $a)
                        <tr id="announcement-{{ $a->id }}">
                            <td>{{ $a->id }}</td>
                            <td class="fw-bold">{{ $a->title }}</td>
                            <td>{{ Str::limit($a->content, 50) }}</td> {{-- Content snippet --}}
                            <td><span class="badge bg-info text-dark">{{ $a->role->name ?? 'All Users' }}</span></td>
                            <td>{{ $a->starts_at ? \Carbon\Carbon::parse($a->starts_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>{{ $a->ends_at ? \Carbon\Carbon::parse($a->ends_at)->format('d-m-Y H:i') : '-' }}</td>
                            <td>{{ $a->created_at ? \Carbon\Carbon::parse($a->created_at)->format('M d, Y H:i') : '' }}</td>

                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-warning me-1 mr-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#announcementModal"
                                            onclick="editAnnouncement({{ $a->id }})"
                                            data-title="{{ $a->title }}"
                                            data-content="{{ $a->content }}"
                                            data-role-id="{{ $a->role_id }}"
                                            data-starts-at="{{ $a->starts_at ? \Carbon\Carbon::parse($a->starts_at)->format('Y-m-d\TH:i') : '' }}"
                                            data-ends-at="{{ $a->ends_at ? \Carbon\Carbon::parse($a->ends_at)->format('Y-m-d\TH:i') : '' }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('announcements.destroy', ['announcement' => $a->id]) }}" method="POST" class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(this)">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-exclamation-triangle me-2"></i> No announcements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3 px-3 pb-3">
        {{ $announcements->links() }}
    </div>
</div>

<div class="modal fade" id="announcementModal" tabindex="-1" aria-labelledby="announcementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="announcementForm" method="POST">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="announcementModalLabel">Create New Announcement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Title</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="Announcement Title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content2" class="form-label fw-bold">Content</label>
                        <textarea name="content" id="content2" class="form-control" rows="5" placeholder="Enter announcement details here..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="role_id" class="form-label fw-bold">Target Role</label>
                        <select name="role_id" id="role_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select the user role that will see this announcement.</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="starts_at" class="form-label fw-bold">Starts At</label>
                            <input type="datetime-local" name="starts_at" id="starts_at" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="ends_at" class="form-label fw-bold">Ends At</label>
                            <input type="datetime-local" name="ends_at" id="ends_at" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="modalSaveButton">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function resetModal() {
        document.getElementById('announcementModalLabel').innerText = 'Create New Announcement';
        document.getElementById('announcementForm').action = '{{ route('announcements.store') }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('title').value = '';
        document.getElementById('content2').value = '';
        document.getElementById('role_id').value = '';
        document.getElementById('starts_at').value = '';
        document.getElementById('ends_at').value = '';
        document.getElementById('modalSaveButton').innerText = 'Save';
        document.getElementById('modalSaveButton').disabled = false;
    }

    function editAnnouncement(id) {
        const button = event.currentTarget;
        const title = button.getAttribute('data-title');
        const content = button.getAttribute('data-content');
        const roleId = button.getAttribute('data-role-id');
        const startsAt = button.getAttribute('data-starts-at');
        const endsAt = button.getAttribute('data-ends-at');

        document.getElementById('announcementModalLabel').innerText = 'Edit Announcement';

        const urlTemplate = '{{ route('announcements.update', ['announcement' => '__ID__'], false) }}';
        const finalUrl = urlTemplate.replace('__ID__', id);

        document.getElementById('announcementForm').action = finalUrl;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('title').value = title || '';
        document.getElementById('content2').value = content || '';
        document.getElementById('role_id').value = roleId || '';
        document.getElementById('starts_at').value = startsAt || '';
        document.getElementById('ends_at').value = endsAt || '';
        document.getElementById('modalSaveButton').innerText = 'Update';
        document.getElementById('modalSaveButton').disabled = false;
    }

    // Form gönderildiğinde butonu devre dışı bırak (Çift tıklama önlemi)
    document.getElementById('announcementForm').addEventListener('submit', function() {
        const btn = document.getElementById('modalSaveButton');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    });

    function confirmDelete(button) {
        if (confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
            button.closest('.delete-form').submit();
        }
    }
</script>
@endsection
