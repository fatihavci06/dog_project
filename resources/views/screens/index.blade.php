@extends('layouts.app')
@section('title', 'Screens')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
                        <th>Hero Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <!-- ==================== EDIT MODAL ==================== -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="editModalLabel">Edit Screen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <form id="screenForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="screen_id">

                        <div class="card shadow mb-4">
                            <div class="card-header bg-light">
                                <h4 class="mb-0 text-primary">🖼️ Hero Image</h4>
                            </div>
                            <div class="card-body">

                                <h6 class="border-bottom pb-2 mb-3 text-secondary">General Settings</h6>

                                <div class="mb-3">
                                    <label class="form-label d-block">Visible</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[hero_image][visible]" id="heroVisibleYes" value="1">
                                        <label class="form-check-label" for="heroVisibleYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[hero_image][visible]" id="heroVisibleNo" value="0">
                                        <label class="form-check-label" for="heroVisibleNo">No</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="heroImageUrl" class="form-label">Image URL</label>
                                    <input type="text" class="form-control" id="heroImageUrl" name="content[hero_image][url]">
                                </div>

                                <div class="mb-3">
                                    <label for="heroImageFile" class="form-label">Upload New</label>
                                    <input type="file" class="form-control" id="heroImageFile" name="hero_image_file">
                                </div>

                                <div class="mb-3">
                                    <label for="heroImagePosition" class="form-label">Position</label>
                                    <select class="form-select" id="heroImagePosition" name="content[hero_image][position]">
                                        <option value="top">Top</option>
                                        <option value="bottom">Bottom</option>
                                    </select>
                                </div>

                                <h6 class="border-bottom pb-2 mb-3 mt-4 text-secondary">Style Settings</h6>

                                <div class="mb-3">
                                    <label for="aspectRatio" class="form-label">Aspect Ratio</label>
                                    <input type="number" step="0.1" class="form-control" id="aspectRatio"
                                        name="content[hero_image][style][aspect_ratio]">
                                </div>

                                <div class="mb-3">
                                    <label for="resizeMode" class="form-label">Resize Mode</label>
                                    <select class="form-select" id="resizeMode" name="content[hero_image][style][resize_mode]">
                                        <option value="cover">Cover</option>
                                        <option value="contain">Contain</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="cornerRadius" class="form-label">Corner Radius</label>
                                    <input type="number" class="form-control" id="cornerRadius" name="content[hero_image][style][corner_radius]">
                                </div>

                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header bg-light">
                                <h4 class="mb-0 text-primary">📝 Text Group</h4>
                            </div>
                            <div class="card-body">

                                <h6 class="border-bottom pb-2 mb-3 text-secondary">Group Settings</h6>

                                <div class="mb-3">
                                    <label class="form-label d-block">Visible</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[text_group][visible]" id="textGroupVisibleYes" value="1">
                                        <label class="form-check-label" for="textGroupVisibleYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[text_group][visible]" id="textGroupVisibleNo" value="0">
                                        <label class="form-check-label" for="textGroupVisibleNo">No</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="positionOnScreen" class="form-label">Position On Screen</label>
                                    <select class="form-select" id="positionOnScreen" name="content[text_group][position_on_screen]">
                                        <option value="center">Center</option>
                                        <option value="top">Top</option>
                                        <option value="bottom">Bottom</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="textAlign" class="form-label">Text Align</label>
                                    <select class="form-select" id="textAlign" name="content[text_group][text_align]">
                                        <option value="center">Center</option>
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label d-block">Overlay On Image</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[text_group][overlay_on_image]" id="overlayYes" value="1">
                                        <label class="form-check-label" for="overlayYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[text_group][overlay_on_image]" id="overlayNo" value="0">
                                        <label class="form-check-label" for="overlayNo">No</label>
                                    </div>
                                </div>


                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <div class="card card-body border-info">
                                            <h6 class="text-info border-bottom pb-2 mb-3">Title</h6>

                                            <div class="mb-3">
                                                <label for="titleText" class="form-label">Text</label>
                                                <input type="text" class="form-control" id="titleText" name="content[text_group][title][text]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="titleColor" class="form-label">Color</label>
                                                <input type="color" class="form-control form-control-color" id="titleColor" name="content[text_group][title][color]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="titleFontSize" class="form-label">Font Size</label>
                                                <input type="number" class="form-control" id="titleFontSize" name="content[text_group][title][font_size]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="titleFontWeight" class="form-label">Font Weight</label>
                                                <select class="form-select" id="titleFontWeight" name="content[text_group][title][font_weight]">
                                                    <option value="bold">Bold</option>
                                                    <option value="normal">Normal</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <div class="card card-body border-info">
                                            <h6 class="text-info border-bottom pb-2 mb-3">Subtitle</h6>

                                            <div class="mb-3">
                                                <label for="subtitleText" class="form-label">Text</label>
                                                <input type="text" class="form-control" id="subtitleText" name="content[text_group][subtitle][text]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="subtitleColor" class="form-label">Color</label>
                                                <input type="color" class="form-control form-control-color" id="subtitleColor" name="content[text_group][subtitle][color]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="subtitleFontSize" class="form-label">Font Size</label>
                                                <input type="number" class="form-control" id="subtitleFontSize" name="content[text_group][subtitle][font_size]">
                                            </div>

                                            <div class="mb-3">
                                                <label for="subtitleMarginTop" class="form-label">Margin Top</label>
                                                <input type="number" class="form-control" id="subtitleMarginTop" name="content[text_group][subtitle][margin_top]">
                                            </div>
                                        </div>
                                    </div>

                                </div> </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header bg-light">
                                <h4 class="mb-0 text-primary">👆 CTA Button</h4>
                            </div>
                            <div class="card-body">

                                <h6 class="border-bottom pb-2 mb-3 text-secondary">Button Settings</h6>

                                <div class="mb-3">
                                    <label class="form-label d-block">Visible</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[cta_button][visible]" id="ctaVisibleYes" value="1">
                                        <label class="form-check-label" for="ctaVisibleYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[cta_button][visible]" id="ctaVisibleNo" value="0">
                                        <label class="form-check-label" for="ctaVisibleNo">No</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="ctaText" class="form-label">Text</label>
                                    <input type="text" class="form-control" id="ctaText" name="content[cta_button][text]">
                                </div>

                                <div class="mb-3">
                                    <label for="actionType" class="form-label">Action Type</label>
                                    <select class="form-select" id="actionType" name="content[cta_button][action_type]">
                                        <option value="navigate">Navigate</option>
                                        <option value="link">Link</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="actionValue" class="form-label">Action Value</label>
                                    <input type="text" class="form-control" id="actionValue" name="content[cta_button][action_value]">
                                </div>

                                <div class="mb-3">
                                    <label for="themeId" class="form-label">Theme ID</label>
                                    <input type="number" class="form-control" id="themeId" name="content[cta_button][theme_id]">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label d-block">Show Icon</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[cta_button][show_icon]" id="showIconYes" value="1">
                                        <label class="form-check-label" for="showIconYes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="content[cta_button][show_icon]" id="showIconNo" value="0">
                                        <label class="form-check-label" for="showIconNo">No</label>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </form>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                url: "{{ route('screens.list') }}",
                dataSrc: "data"
            },
            columns: [{
                    data: "id"
                },
                {
                    data: "screen_slug"
                },
                {
                    data: "content.hero_image.visible",
                    render: val => val ? `<span class='badge bg-success'>Yes</span>` :
                        `<span class='badge bg-danger'>No</span>`
                },
                {
                    data: null,
                    render: data =>
                        `<button class="btn btn-warning btn-sm editBtn" data-id="${data.id}">Edit</button>`
                }
            ]
        });


        $(document).on("click", ".editBtn", function() {
            let id = $(this).data("id");

            $.get(`/mobile-app-settings/screens/get/${id}`, res => {

                $("#screen_id").val(res.id);

                function setVal(path, value) {
                    let input = $(`[name="${path}"]`);
                    if (input.attr("type") === "radio") {
                        $(`[name="${path}"][value="${value}"]`).prop("checked", true);
                    } else {
                        input.val(value);
                    }
                }

                setVal(`content[hero_image][visible]`, res.content.hero_image.visible);
                setVal(`content[hero_image][url]`, res.content.hero_image.url);
                setVal(`content[hero_image][position]`, res.content.hero_image.position);
                setVal(`content[hero_image][style][aspect_ratio]`, res.content.hero_image.style
                    .aspect_ratio);
                setVal(`content[hero_image][style][resize_mode]`, res.content.hero_image.style.resize_mode);
                setVal(`content[hero_image][style][corner_radius]`, res.content.hero_image.style
                    .corner_radius);

                setVal(`content[text_group][visible]`, res.content.text_group.visible);
                setVal(`content[text_group][position_on_screen]`, res.content.text_group
                    .position_on_screen);
                setVal(`content[text_group][text_align]`, res.content.text_group.text_align);
                setVal(`content[text_group][overlay_on_image]`, res.content.text_group.overlay_on_image);

                setVal(`content[text_group][title][text]`, res.content.text_group.title.text);
                setVal(`content[text_group][title][color]`, res.content.text_group.title.color);
                setVal(`content[text_group][title][font_size]`, res.content.text_group.title.font_size);
                setVal(`content[text_group][title][font_weight]`, res.content.text_group.title.font_weight);

                setVal(`content[text_group][subtitle][text]`, res.content.text_group.subtitle.text);
                setVal(`content[text_group][subtitle][color]`, res.content.text_group.subtitle.color);
                setVal(`content[text_group][subtitle][font_size]`, res.content.text_group.subtitle
                    .font_size);
                setVal(`content[text_group][subtitle][margin_top]`, res.content.text_group.subtitle
                    .margin_top);

                setVal(`content[cta_button][visible]`, res.content.cta_button.visible);
                setVal(`content[cta_button][text]`, res.content.cta_button.text);
                setVal(`content[cta_button][action_type]`, res.content.cta_button.action_type);
                setVal(`content[cta_button][action_value]`, res.content.cta_button.action_value);
                setVal(`content[cta_button][theme_id]`, res.content.cta_button.theme_id);
                setVal(`content[cta_button][show_icon]`, res.content.cta_button.show_icon);
                setVal(`content[text_group][position_on_screen]`, res.content.text_group
                .position_on_screen);
                setVal(`content[text_group][text_align]`, res.content.text_group.text_align);
                setVal(`content[text_group][overlay_on_image]`, res.content.text_group.overlay_on_image);


                $('#editModal').modal('show');
            });
        });


        $("#saveBtn").click(() => {
            let id = $("#screen_id").val();
            let formData = new FormData(document.getElementById("screenForm"));

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('input[name="_token"]').val()
                }
            });

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
