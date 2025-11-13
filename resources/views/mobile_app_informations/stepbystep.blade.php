@extends('layouts.app')

@section('title', 'Mobile Steps Information')

@section('content')

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: "success",
        title: "{{ session('success') }}",
        toast: true,
        position: "top-end",
        timer: 2500,
        showConfirmButton: false
    });
});
</script>
@endif

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: "error",
        title: "Validation Error",
        html: `{!! implode('<br>', $errors->all()) !!}`,
    });
});
</script>
@endif


<div class="card shadow-sm">
    <div class="card-header">
        <h4>Mobile App Step Information</h4>
        <small class="text-muted">Only update allowed. No add/delete.</small>
    </div>

    <div class="card-body">

        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Step</th>

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
                        <td><strong>{{ $item->step_number }}</strong></td>

                        @foreach($languages as $lang)
                            <td>{{ $item->translate('title', $lang->code) }}</td>
                        @endforeach

                        <td>
                            @if($item->image_path)
                                <img src="{{ $item->image_path }}" width="80" class="rounded border">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>

                        <td>
                            <button class="btn btn-warning btn-sm editBtn"
                                data-id="{{ $item->id }}"
                                data-step_number="{{ $item->step_number }}"
                                data-image="{{ $item->image_path }}"

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
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editForm" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5>Edit Step Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id" id="edit_id">

                <label>Step Number</label>
                <input type="number" name="step_number" class="form-control mb-3" id="edit_step_number" readonly>

                @foreach($languages as $lang)
                    <div class="mb-3">
                        <label>Title ({{ strtoupper($lang->code) }})</label>
                        <input type="text"
                               name="title[{{ $lang->code }}]"
                               id="edit_title_{{ $lang->code }}"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label>Description ({{ strtoupper($lang->code) }})</label>
                        <textarea name="description[{{ $lang->code }}]"
                                  id="edit_description_{{ $lang->code }}"
                                  rows="2"
                                  class="form-control"
                                  required></textarea>
                    </div>
                @endforeach

                <label>Image</label>
                <input type="file" name="image_path" class="form-control mb-2" accept="image/*">


                <img id="preview_image" src="" width="120" class="rounded mt-2 d-none border">
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-success">Update</button>
            </div>

        </form>
    </div>
</div>

@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {

            let id = btn.dataset.id;

            // Form action
            document.getElementById('editForm').action = "/mobile-app-information/mobile-steps/" + id;

            // Hidden id
            document.getElementById('edit_id').value = id;

            // Step number (readonly)
            document.getElementById('edit_step_number').value = btn.dataset.step_number;

            // Çok dilli alanlar
            @foreach($languages as $lang)
                document.getElementById('edit_title_{{ $lang->code }}').value =
                    btn.dataset["title{{ $lang->code }}"] ?? '';

                document.getElementById('edit_description_{{ $lang->code }}').value =
                    btn.dataset["description{{ $lang->code }}"] ?? '';
            @endforeach

            // Image preview
            let imagePath = btn.dataset.image;
            let preview = document.getElementById('preview_image');

            if(imagePath){
                preview.src = imagePath;
                preview.classList.remove('d-none');
            } else {
                preview.classList.add('d-none');
            }
        });
    });

});
</script>
@endsection
