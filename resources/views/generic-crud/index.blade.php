@extends('layouts.app')

@section('title', ($model === 'Bread' ? 'Breed' : $model) . ' List')


@section('content')

    <div class="container">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between">
                @php
                    $titleModel = $model === 'Bread' ? 'Breed' : $model;
                @endphp <h4>{{ $titleModel }} List</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        + Add New
                    </button>
            </div>

            <div class="card-body">

                {{-- Search --}}
                <form method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search..."
                            value="{{ $search ?? '' }}">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </form>

                {{-- Table --}}
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>

                            {{-- ICON COLUMN (only visible if model is Bread) --}}
                            @if ($model === 'Vibe')
                                <th>Icon</th>
                            @endif

                            @foreach ($languages as $lang)
                                <th>{{ strtoupper($lang->code) }} Name</th>
                            @endforeach

                            <th width="150">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>

                                {{-- ICON SHOW --}}
                                @if ($model === 'Vibe')
                                    <td>
                                        @if (!empty($item->icon_path))
                                            <img src="{{ $item->icon_path }}"
                                                style="width:40px;height:40px;object-fit:contain;" alt="icon">
                                        @else
                                            <span class="text-muted">No Icon</span>
                                        @endif
                                    </td>
                                @endif

                                @foreach ($languages as $lang)
                                    <td>{{ $item->translate('name', $lang->code) }}</td>
                                @endforeach

                                <td>

                                    {{-- EDIT BUTTON --}}
                                    <button class="btn btn-warning btn-sm editBtn" data-id="{{ $item->id }}"
                                        {{-- icon data --}}
                                        @if ($model === 'Vibe') data-icon="{{ $item->icon_url }}" @endif
                                        {{-- lang data --}}
                                        @foreach ($languages as $lang)
                                        data-name{{ $lang->code }}="{{ $item->translate('name', $lang->code) }}" @endforeach
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                        Edit
                                    </button>

                                    {{-- DELETE BUTTON --}}
                                    <form method="POST" action="{{ route('generic.destroy', [$model, $item->id]) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm deleteBtn">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

                <div class="d-flex justify-content-center mt-3">
                    {{ $items->links() }}
                </div>

            </div>
        </div>
    </div>


    {{-- ADD MODAL --}}
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('generic.store', $model) }}" class="modal-content"
                enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <h5>Add New {{ $model }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- ICON UPLOAD (only for Bread) --}}
                    @if ($model === 'Vibe')
                        <div class="mb-3">
                            <label class="form-label">Icon (PNG / SVG)</label>
                            <input type="file" name="icon" class="form-control" accept=".svg,image/svg+xml,image/png">
                        </div>
                    @endif

                    {{-- Multi-lang fields --}}
                    @foreach ($languages as $lang)
                        <label>{{ $lang->name }} ({{ $lang->code }})</label>
                        <input type="text" name="name[{{ $lang->code }}]" class="form-control mb-2" required>
                    @endforeach

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>

            </form>
        </div>
    </div>


    {{-- EDIT MODAL --}}
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <form method="POST" class="modal-content" id="editForm" enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5>Edit {{ $model }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    {{-- ICON AREA ONLY FOR BREAD --}}
                    @if ($model === 'Vibe')
                        <div class="mb-3">
                            <label class="form-label">Current Icon</label><br>

                            <img id="edit_icon_preview" src=""
                                style="max-width:50px;max-height:50px;object-fit:contain;display:none;">

                            <label class="form-label mt-2">Change Icon</label>
                            <input type="file" name="icon" class="form-control" accept=".svg,image/svg+xml,image/png">

                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="remove_icon" name="remove_icon"
                                    value="1">
                                <label class="form-check-label" for="remove_icon">
                                    Remove Icon
                                </label>
                            </div>
                        </div>
                    @endif

                    {{-- Multi-lang edit inputs --}}
                    @foreach ($languages as $lang)
                        <label>{{ $lang->name }} ({{ $lang->code }})</label>
                        <input type="text" name="name[{{ $lang->code }}]" id="edit_name_{{ $lang->code }}"
                            class="form-control mb-2" required>
                    @endforeach

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>

@endsection


@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* --------------------------------------
               EDIT BUTTON (Fill Modal Data)
            -------------------------------------- */
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', () => {

                    let id = btn.dataset.id;
                    document.getElementById('edit_id').value = id;

                    // Fill multilanguage names
                    @foreach ($languages as $lang)
                        document.getElementById('edit_name_{{ $lang->code }}').value =
                            btn.dataset["name{{ $lang->code }}"];
                    @endforeach

                    // ICON HANDLING (Bread only)
                    @if ($model === 'Vibe')
                        const preview = document.getElementById('edit_icon_preview');
                        const iconPath = btn.dataset.icon;
                        const removeIconCheckbox = document.getElementById('remove_icon');

                        if (iconPath) {
                            preview.src = "{{ asset('storage') }}/" + iconPath;
                            preview.style.display = "inline-block";
                        } else {
                            preview.src = "";
                            preview.style.display = "none";
                        }

                        removeIconCheckbox.checked = false;
                    @endif

                    document.getElementById('editForm').action = `/{{ $model }}/${id}`;
                });
            });


            /* --------------------------------------
               DELETE CONFIRM
            -------------------------------------- */
            document.querySelectorAll('.deleteBtn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "This item will be permanently deleted!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then(result => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    })
                });
            });

            /* --------------------------------------
               SUCCESS TOAST
            -------------------------------------- */
            @if (session('success'))
                Swal.fire({
                    icon: "success",
                    title: "{{ session('success') }}",
                    toast: true,
                    position: "top-end",
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif

        });
    </script>
@endsection
