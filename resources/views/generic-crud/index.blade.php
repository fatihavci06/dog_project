@extends('layouts.app')

@section('title', $model . ' List')

@section('content')

    <div class="container">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif


        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>{{ $model }} List</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    + Add New
                </button>
            </div>

            <div class="card-body">
<form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search..."
                   value="{{ $search ?? '' }}">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>

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

                                @foreach ($languages as $lang)
                                    <td>{{ $item->translate('name', $lang->code) }}</td>
                                @endforeach

                                <td>
                                    <button class="btn btn-warning btn-sm editBtn" data-id="{{ $item->id }}"
                                        @foreach ($languages as $lang)
        data-name{{ $lang->code }}="{{ $item->translate('name', $lang->code) }}" @endforeach
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                        Edit
                                    </button>


                                    <form method="POST" action="{{ route('generic.destroy', [$model, $item->id]) }}"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" >
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


    {{-- Add Modal --}}
    <div class="modal fade" id="addModal">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('generic.store', $model) }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5>Add New {{ $model }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

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


    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal">
        <div class="modal-dialog">
            <form method="POST" class="modal-content" id="editForm">
                @csrf

                <div class="modal-header">
                    <h5>Edit {{ $model }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    @foreach ($languages as $lang)
                        <label>{{ $lang->name }} ({{ $lang->code }})</label>
                        <input type="text" class="form-control mb-2" name="name[{{ $lang->code }}]"
                            id="edit_name_{{ $lang->code }}" required>
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
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ----------------------------------------------
                EDIT BUTTON MODAL
            ---------------------------------------------- */
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', () => {

                    let id = btn.dataset.id;
                    document.getElementById('edit_id').value = id;

                    @foreach ($languages as $lang)
                        document.getElementById('edit_name_{{ $lang->code }}').value =
                            btn.dataset["name{{ $lang->code }}"] ?? "";
                    @endforeach

                    // Dinamik form action
                    document.getElementById('editForm').action = `/{{ $model }}/${id}`;
                });
            });


            /* ----------------------------------------------
                DELETE BUTTON (SweetAlert Confirm)
            ---------------------------------------------- */
            document.querySelectorAll('.btn-danger').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (!this.closest('form')) return;

                    e.preventDefault(); // formu hemen göndermesin

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
                    });
                });
            });


            /* ----------------------------------------------
                SHOW TOAST WHEN SESSION SUCCESS EXISTS
            ---------------------------------------------- */
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

