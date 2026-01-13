@extends('layouts.app')
@section('title', 'Help & Support Settings')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .modal-header.bg-edit { background-color: #f39c12; color: white; }
    .modal-header.bg-create { background-color: #2ecc71; color: white; }
</style>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Help & Support Management</h4>
        <button class="btn btn-light btn-sm" id="createNewBtn">+ Add New Support Content</button>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="supportTable">
            <thead class="table-dark">
                <tr>
                    <th>Language</th>
                    <th>Title</th>
                    <th>Email</th>
                    <th>Website</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="supportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="modalTitle">Support Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="supportForm">
                    @csrf
                    <input type="hidden" id="support_id" name="id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Language Selection</label>
                            <select class="form-select" name="language_code" id="language_code">
                                <option value="">Select Language</option>
                                @foreach(\App\Models\Language::where('is_active', true)->get() as $lang)
                                    <option value="{{ $lang->code }}">{{ $lang->name }} ({{ $lang->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Support Title</label>
                            <input type="text" class="form-control" name="title" id="title">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                    </div>

                    <hr>
                    <h6 class="text-primary mb-3"><i class="fas fa-envelope"></i> Contact Information</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="email">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="phone">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Website URL</label>
                            <input type="url" class="form-control" name="website_url" id="website_url">
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-info mb-3"><i class="fab fa-share-alt"></i> Social Media Links</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Instagram</label>
                            <input type="url" class="form-control" name="instagram_url" id="instagram_url">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">TikTok</label>
                            <input type="url" class="form-control" name="tiktok_url" id="tiktok_url">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">X (Twitter)</label>
                            <input type="url" class="form-control" name="x_url" id="x_url">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBtn">Save Content</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let table = $('#supportTable').DataTable({
    ajax: {
        url: "/admin/support/list", // Bu route'u tanımlaman gerekecek
        dataSrc: "data"
    },
    columns: [
        { data: "language_code", render: d => `<span class="badge bg-info">${d.toUpperCase()}</span>` },
        { data: "title" },
        { data: "email" },
        { data: "website_url" },
        {
            data: null,
            render: d => `
                <button class="btn btn-warning btn-sm editBtn" data-id="${d.id}">Edit</button>
                <button class="btn btn-danger btn-sm deleteBtn" data-id="${d.id}">Delete</button>
            `
        }
    ]
});

// Create New Modal
$("#createNewBtn").click(function() {
    $("#supportForm")[0].reset();
    $("#support_id").val("");
    $("#modalHeader").removeClass("bg-edit").addClass("bg-create");
    $("#modalTitle").text("Add New Support Content");
    $('#supportModal').modal('show');
});

// Edit Modal
$(document).on("click", ".editBtn", function () {
    let id = $(this).data("id");
    $.get(`/admin/support/get/${id}`, res => {
        $("#support_id").val(res.id);
        $("#language_code").val(res.language_code);
        $("#title").val(res.title);
        $("#description").val(res.description);
        $("#email").val(res.email);
        $("#phone").val(res.phone);
        $("#website_url").val(res.website_url);
        $("#instagram_url").val(res.instagram_url);
        $("#tiktok_url").val(res.tiktok_url);
        $("#x_url").val(res.x_url);

        $("#modalHeader").removeClass("bg-create").addClass("bg-edit");
        $("#modalTitle").text("Edit Support Content (" + res.language_code.toUpperCase() + ")");
        $('#supportModal').modal('show');
    });
});

// Save (Create or Update)
$("#saveBtn").click(() => {
    let id = $("#support_id").val();
    let url = id ? `/admin/support/update/${id}` : `/admin/support/store`;
    let formData = $("#supportForm").serialize();

    $.post(url, formData, res => {
        Swal.fire("Success", "Content saved successfully", "success");
        $('#supportModal').modal('hide');
        table.ajax.reload();
    }).fail(err => {
        Swal.fire("Error", "Something went wrong!", "error");
    });
});

// Delete
$(document).on("click", ".deleteBtn", function () {
    let id = $(this).data("id");
    Swal.fire({
        title: 'Are you sure?',
        text: "This content will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`/admin/support/delete/${id}`, { _token: "{{ csrf_token() }}" }, res => {
                Swal.fire("Deleted!", "Content deleted.", "success");
                table.ajax.reload();
            });
        }
    });
});
</script>
@endsection
