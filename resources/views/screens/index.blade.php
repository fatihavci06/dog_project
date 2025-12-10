@extends('layouts.app')
@section('title', 'Screens')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .layout-card {
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 10px;
        transition: .2s;
    }
    .layout-card.active {
        border-color: #0d6efd;
        box-shadow: 0 0 10px rgba(13,110,253,.6);
    }
</style>
@endsection

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Screens</h4>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="screenTable">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Slug</th>
                    <th>Layout</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- ==================== EDIT MODAL ==================== -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Edit Screen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="screenForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="screen_id">

                    <!-- Layout Selection -->
                    <h6 class="mb-3 fw-bold">📐 Layout Type</h6>
                    <div class="row mb-4" id="layoutList">
                        @php
                             $layouts = [
        ["key" => "hero_overlay_bottom", "image" => asset('screens/full_hero_static.png')],
        ["key" => "hero_overlay_center", "image" => asset('screens/hero_overlay_bottom.png')],
        ["key" => "hero_overlay_top", "image" => asset('screens/image_top_text_middle.png')],
        ["key" => "full_center", "image" => asset('screens/text_only.png')],
        ["key" => "full_bottom", "image" => asset('screens/text_top_image_bottom.png')],
    ];
                        @endphp

                        @foreach($layouts as $layout)
                        <div class="col-4 mb-3">
                            <label class="layout-card d-block p-1">
                                <input type="radio" name="content[layout_type]" value="{{ $layout['key'] }}" class="d-none"/>
                                <img src="{{ $layout['image'] }}" class="img-fluid rounded">
                                <div class="text-center small mt-1">{{ $layout['key'] }}</div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div class="row">


                        <div class="col-md-6">
                            <label class="form-label">Media URL</label>
                            <input type="text" class="form-control" name="content[hero_image][url]">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Upload New</label>
                        <input type="file" class="form-control" name="hero_image_file">
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="content[title]" maxlength="50">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subtitle</label>
                        <input type="text" class="form-control" name="content[subtitle]" maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">CTA Text</label>
                        <input type="text" class="form-control" name="content[cta_text]" maxlength="20">
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="saveBtn">Save Changes</button>
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

let table = $('#screenTable').DataTable({
    ajax: {
        url: "/mobile-app-settings/screens/list",
        dataSrc: "data"
    },
    columns: [
        { data: "id" },
        { data: "screen_slug" },
        { data: "content.layout_type" },
        {
            data: null,
            render: d => `<button class="btn btn-warning btn-sm editBtn" data-id="${d.id}">Edit</button>`
        }
    ]
});

$(document).on("click", ".editBtn", function () {
    let id = $(this).data("id");

    $.get(`/mobile-app-settings/screens/get/${id}`, res => {
        $("#screen_id").val(res.id);

        function setRadio(name, val){
            $(`[name="${name}"][value="${val}"]`).prop("checked", true);
        }

        function setInput(name, val){
            $(`[name="${name}"]`).val(val);
        }

        setRadio(`content[layout_type]`, res.content.layout_type);
        setRadio(`content[hero_type]`, res.content.hero_type);
        setInput(`content[hero_image][url]`, res.content.hero_image.url);
        setInput(`content[title]`, res.content.title);
        setInput(`content[subtitle]`, res.content.subtitle);
        setInput(`content[cta_text]`, res.content.cta_text);

        // highlight selected layout card
        $(".layout-card").removeClass("active");
        $(`[name="content[layout_type]"][value="${res.content.layout_type}"]`)
            .closest(".layout-card").addClass("active");

        $('#editModal').modal('show');
    });
});

// On layout card click highlight
$(document).on("change", `input[name="content[layout_type]"]`, function(){
    $(".layout-card").removeClass("active");
    $(this).closest(".layout-card").addClass("active");
});

$("#saveBtn").click(() => {
    let id = $("#screen_id").val();
    let formData = new FormData(document.getElementById("screenForm"));

    $.ajax({
        url: `/mobile-app-settings/screens/update/${id}`,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: res => {
            Swal.fire("Success", res.message, "success");
            $('#editModal').modal('hide');
            table.ajax.reload();
        }
    });

});

</script>
@endsection
