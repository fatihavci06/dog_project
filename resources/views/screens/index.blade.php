@extends('layouts.app')
@section('title', 'Screens Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* === KART VE TELEFON GÖRÜNÜMÜ STİLLERİ === */

    .layout-card {
        cursor: pointer;
        border: 1px solid #dee2e6;
        border-radius: 16px;
        transition: all 0.2s ease-in-out;
        background: #fff;
        overflow: hidden;
        height: 100%;
        position: relative;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }

    .layout-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        border-color: #adb5bd;
    }

    /* TELEFON EKRANI ALANI */
    .layout-img-wrapper {
        width: 100%;
        aspect-ratio: 9/16; /* Telefon Oranı */
        background-color: #f8f9fa;
        border-bottom: 1px solid #f0f0f0;
        overflow: hidden;
        position: relative;
    }

    .layout-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top center;
        transition: transform 0.4s;
        display: block;
    }

    .layout-card:hover .layout-img-wrapper img {
        transform: scale(1.05);
    }

    /* Başlık */
    .layout-title {
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 0.95rem;
        color: #495057;
        background: white;
        margin-top: auto;
    }

    /* SEÇİLİ DURUM */
    .layout-card.active {
        border: 2px solid #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
    }
    .layout-card.active .layout-title {
        color: #0d6efd;
        background-color: #f8fbff;
        font-weight: bold;
    }
    .layout-card.active::before {
        content: "✔";
        position: absolute;
        top: 10px;
        right: 10px;
        width: 28px;
        height: 28px;
        background: #0d6efd;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Görsel Önizleme Kutusu (Sağ Panel) */
    .img-preview-box {
        height: 120px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #ced4da;
        border-radius: 8px;
        overflow: hidden;
    }
    .img-preview-box img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    /* SCROLL AYARLARI */
    /* Sol menü ve sağ içerik bağımsız scroll olsun */
    .scrollable-panel {
        overflow-y: auto;
        overflow-x: hidden; /* Yatay scrollu engelle */
        height: 100%;
    }

    /* Bootstrap Row negatif margin düzeltmesi */
    .fix-row-overflow {
        padding-left: 4px;
        padding-right: 4px;
        padding-top: 4px;
    }

    .nav-pills .nav-link { color: #495057; border-radius: 0.5rem; }
    .nav-pills .nav-link.active { background-color: #0d6efd; color: white; }
</style>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white py-3">
        <h4 class="mb-0 text-primary"><i class="bi bi-phone"></i> Mobile App Screens</h4>
    </div>

    <div class="card-body">
        <table class="table table-hover align-middle" id="screenTable">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Screen Slug</th>
                    <th>Status</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title">Edit Screen Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0" style="height: 70vh; min-height: 500px;"> <form id="screenForm" enctype="multipart/form-data" class="h-100">
                    @csrf
                    <input type="hidden" id="screen_id">

                    <div class="d-flex h-100">

                        <div class="bg-light border-end scrollable-panel" style="width: 220px; min-width: 220px;">
                            <div class="nav flex-column nav-pills p-3" id="v-pills-tab" role="tablist">
                                @foreach($languages as $index => $lang)
                                <button class="nav-link text-start {{ $index == 0 ? 'active' : '' }} mb-2"
                                        id="v-pills-{{ $lang->code }}-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#v-pills-{{ $lang->code }}"
                                        type="button">
                                    <img src="https://flagcdn.com/20x15/{{ $lang->code == 'en' ? 'gb' : $lang->code }}.png" class="me-2 shadow-sm">
                                    {{ $lang->name }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex-grow-1 p-4 bg-white scrollable-panel">
                            <div class="tab-content" id="v-pills-tabContent">

                                @foreach($languages as $index => $lang)
                                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="v-pills-{{ $lang->code }}">

                                    @php
                                        // Layout Verileri
                                        $layouts = [
                                            ["key" => "hero_overlay_bottom", "image" => asset('screens/full_hero_static.png'), "label" => "Overlay Bottom"],
                                            ["key" => "hero_overlay_center", "image" => asset('screens/hero_overlay_bottom.png'), "label" => "Overlay Center"],
                                            ["key" => "hero_overlay_top", "image" => asset('screens/image_top_text_middle.png'), "label" => "Img Top / Txt Mid"],
                                            ["key" => "full_center", "image" => asset('screens/text_only.png'), "label" => "Text Only"],
                                            ["key" => "full_bottom", "image" => asset('screens/text_top_image_bottom.png'), "label" => "Txt Top / Img Btm"],
                                        ];

                                        if($lang->code == 'tr') {
                                            $layouts = [
                                                ["key" => "hero_overlay_bottom", "image" => asset('screens/full_hero_static_tr.png'), "label" => "Resim Üstü (Alt)"],
                                                ["key" => "hero_overlay_center", "image" => asset('screens/hero_overlay_bottom_tr.png'), "label" => "Resim Üstü (Orta)"],
                                                ["key" => "hero_overlay_top", "image" => asset('screens/image_top_text_middle_tr.png'), "label" => "Resim Üst / Metin Orta"],
                                                ["key" => "full_center", "image" => asset('screens/text_only_tr.png'), "label" => "Sadece Metin"],
                                                ["key" => "full_bottom", "image" => asset('screens/text_top_image_bottom_tr.png'), "label" => "Metin Üst / Resim Alt"],
                                            ];
                                        }

                                        $labelLayout = $lang->code == 'tr' ? 'Şablon Seçimi' : 'Select Layout Template';
                                        $labelText   = $lang->code == 'tr' ? 'Metin İçeriği' : 'Text Content';
                                        $labelMedia  = $lang->code == 'tr' ? 'Medya / Görsel' : 'Media Settings';
                                    @endphp

                                    <h6 class="fw-bold text-secondary mb-3">📐 {{ $labelLayout }} <small class="text-muted">({{ strtoupper($lang->code) }})</small></h6>

                                    <div class="row g-4 mb-5 fix-row-overflow">
                                        @foreach($layouts as $layout)
                                        <div class="col-12 col-md-6 col-xl-4">
                                            <label class="layout-card h-100 w-100">
                                                <input type="radio"
                                                       name="content[translations][{{ $lang->code }}][layout_type]"
                                                       value="{{ $layout['key'] }}"
                                                       class="d-none layout-radio"
                                                       data-lang="{{ $lang->code }}">

                                                <div class="layout-img-wrapper">
                                                    <img src="{{ $layout['image'] }}" alt="{{ $layout['label'] }}">
                                                </div>

                                                <div class="layout-title">{{ $layout['label'] }}</div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>

                                    <hr class="text-muted opacity-25">

                                    <div class="row">
                                        <div class="col-lg-8">
                                            <h6 class="fw-bold text-secondary mb-3">📝 {{ $labelText }}</h6>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Title</label>
                                                <input type="text" class="form-control"
                                                       name="content[translations][{{ $lang->code }}][title]"
                                                       placeholder="{{ $lang->code == 'tr' ? 'Başlık giriniz...' : 'Enter title...' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Subtitle</label>
                                                <textarea class="form-control" rows="2"
                                                          name="content[translations][{{ $lang->code }}][subtitle]"
                                                          placeholder="{{ $lang->code == 'tr' ? 'Alt başlık giriniz...' : 'Enter subtitle...' }}"></textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">CTA Button Text</label>
                                                <input type="text" class="form-control w-50"
                                                       name="content[translations][{{ $lang->code }}][cta_text]">
                                            </div>
                                        </div>

                                        <div class="col-lg-4 border-start">
                                            <h6 class="fw-bold text-secondary mb-3">🖼️ {{ $labelMedia }}</h6>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Current Image</label>
                                                <div id="preview-{{ $lang->code }}" class="img-preview-box mb-2">
                                                    <span class="text-muted small">Empty</span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm mb-2 text-muted bg-light"
                                                       name="content[translations][{{ $lang->code }}][hero_image][url]" readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Upload New</label>
                                                <input type="file" class="form-control form-control-sm"
                                                       name="hero_image_file_{{ $lang->code }}">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                @endforeach

                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary px-4" id="saveBtn">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const languages = @json($languages->pluck('code'));

    let table = $('#screenTable').DataTable({
        ajax: {
            url: "{{ route('screens.list') }}",
            dataSrc: "data"
        },
        columns: [
            { data: "id" },
            { data: "screen_slug", render: d => `<span class="badge bg-dark text-uppercase">${d}</span>` },
            {
                data: null,
                render: d => {
                    let count = (d.content && d.content.translations) ? Object.keys(d.content.translations).length : 0;
                    return `<small class="text-muted">${count} languages configured</small>`;
                }
            },
            {
                data: null,
                className: "text-end",
                render: d => `<button class="btn btn-sm btn-outline-primary editBtn" data-id="${d.id}">Edit <i class="bi bi-pencil"></i></button>`
            }
        ]
    });

    $(document).on("click", ".editBtn", function () {
        let id = $(this).data("id");
        $("#screenForm")[0].reset();
        $(".layout-card").removeClass("active");
        $(".img-preview-box").html('<span class="text-muted small">Empty</span>');

        const firstTabTriggerEl = document.querySelector('#v-pills-tab button:first-child');
        if(firstTabTriggerEl) {
            bootstrap.Tab.getInstance(firstTabTriggerEl)?.show() || new bootstrap.Tab(firstTabTriggerEl).show();
        }

        $.get(`/mobile-app-settings/screens/get/${id}`, res => {
            $("#screen_id").val(res.id);
            if (res.content && res.content.translations) {
                languages.forEach(code => {
                    let transData = res.content.translations[code];
                    if (transData) {
                        if(transData.layout_type){
                            let radio = $(`input[name="content[translations][${code}][layout_type]"][value="${transData.layout_type}"]`);
                            radio.prop("checked", true);
                            radio.closest(".layout-card").addClass("active");
                        }
                        $(`[name="content[translations][${code}][title]"]`).val(transData.title || '');
                        $(`[name="content[translations][${code}][subtitle]"]`).val(transData.subtitle || '');
                        $(`[name="content[translations][${code}][cta_text]"]`).val(transData.cta_text || '');

                        if(transData.hero_image && transData.hero_image.url) {
                            let url = transData.hero_image.url;
                            $(`[name="content[translations][${code}][hero_image][url]"]`).val(url);
                            $(`#preview-${code}`).html(`<img src="${url}">`);
                        }
                    }
                });
            }
            $('#editModal').modal('show');
        });
    });

    $(document).on("change", ".layout-radio", function(){
        let langCode = $(this).data("lang");
        $(`#v-pills-${langCode} .layout-card`).removeClass("active");
        $(this).closest(".layout-card").addClass("active");
    });

    $("#saveBtn").click(function() {
        let id = $("#screen_id").val();
        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        let formData = new FormData(document.getElementById("screenForm"));

        $.ajax({
            url: `/mobile-app-settings/screens/update/${id}`,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: res => {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved Successfully',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                $('#editModal').modal('hide');
                table.ajax.reload();
            },
            error: err => {
                Swal.fire("Error", "Something went wrong!", "error");
            },
            complete: () => {
                btn.prop('disabled', false).html('<i class="bi bi-save"></i> Save Changes');
            }
        });
    });
</script>
@endsection
