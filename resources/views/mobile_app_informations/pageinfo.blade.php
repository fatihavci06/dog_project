@extends('layouts.app')

@section('title', 'Page Info')

@section('content')

<div class="card">
    <div class="card-header">
        <h4>Page Info</h4>
    </div>

    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Page Name</th>

                    @foreach($languages as $lang)
                        <th>Title ({{ strtoupper($lang->code) }})</th>
                    @endforeach

                    <th>Image</th>
                    <th width="120">Actions</th>
                </tr>
            </thead>

            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->page_name }}</td>

                        @foreach($languages as $lang)
                            <td>{{ $item->translate('title', $lang->code) }}</td>
                        @endforeach

                        <td>
                            @if($item->image_path)
                                <img src="{{ $item->image_path }}" width="80">
                            @endif
                        </td>

                        <td>
                            <button class="btn btn-warning btn-sm editBtn"
                                data-id="{{ $item->id }}"
                                data-page_name="{{ $item->page_name }}"
                                @foreach($languages as $lang)
                                    data-title{{ $lang->code }}="{{ $item->translate('title', $lang->code) }}"
                                    data-description{{ $lang->code }}="{{ $item->translate('description', $lang->code) }}"
                                @endforeach
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>


{{-- EDIT MODAL --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editForm" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5>Edit Page</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id" id="edit_id">

                <label>Page Name</label>
                <input type="text" name="page_name" id="edit_page_name"
                       class="form-control mb-3" readonly>

                @foreach($languages as $lang)
                    <label>Title ({{ $lang->code }})</label>
                    <input type="text" name="title[{{ $lang->code }}]"
                           id="edit_title_{{ $lang->code }}"
                           class="form-control mb-2" required>

                    <label>Description ({{ $lang->code }})</label>
                    <textarea name="description[{{ $lang->code }}]"
                              id="edit_description_{{ $lang->code }}"
                              rows="2"
                              class="form-control mb-3" required></textarea>
                @endforeach

                <label>Image</label>
                <input type="file" name="image_path" class="form-control">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button class="btn btn-success" type="submit">Update</button>
            </div>

        </form>
    </div>
</div>

@endsection


@section('scripts')
    {{-- Eğer layout'ta zaten ekli değilse bu satır kalsın --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // EDIT BUTTON
            document.querySelectorAll('.editBtn').forEach(btn => {
                btn.addEventListener('click', () => {

                    let id = btn.dataset.id;

                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_page_name').value = btn.dataset.page_name;

                    @foreach($languages as $lang)
                        document.getElementById('edit_title_{{ $lang->code }}').value =
                            btn.dataset["title{{ $lang->code }}"] ?? '';

                        document.getElementById('edit_description_{{ $lang->code }}').value =
                            btn.dataset["description{{ $lang->code }}"] ?? '';
                    @endforeach

                    document.getElementById('editForm').action = "/mobile-app-information/page-info/" + id;
                });
            });

            // SUCCESS MESSAGE
            @if(session('success'))
            Swal.fire({
                icon: "success",
                title: "{{ session('success') }}",
                toast: true,
                position: "top-end",
                timer: 2500,
                showConfirmButton: false
            });
            @endif

            // VALIDATION ERRORS
            @if ($errors->any())
            Swal.fire({
                icon: "error",
                title: "Validation Error",
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
            @endif

        });
    </script>
@endsection
