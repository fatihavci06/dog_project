@extends('layouts.app')
@section('title', 'Screens Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    /* Layout Kartları */
    .layout-card {
        cursor: pointer;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: .2s;
        position: relative;
        overflow: hidden;
    }
    .layout-card:hover {
        border-color: #adb5bd;
        background-color: #f8f9fa;
    }
    /* Seçili Kart Stili */
    .layout-card.active {
        border-color: #0d6efd;
        background-color: #f0f7ff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }
    .layout-card.active::before {
        content: "✔";
        position: absolute;
        top: 0;
        right: 0;
        background: #0d6efd;
        color: white;
        padding: 2px 8px;
        border-bottom-left-radius: 8px;
        font-size: 12px;
        font-weight: bold;
    }

    /* Görsel Önizleme Kutusu */
    .img-preview-box {
        height: 100px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        overflow: hidden;
    }
    .img-preview-box img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }

    /* Sol Menü Stilleri */
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

            <div class="modal-body p-0">
                <form id="screenForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="screen_id">

                    <div class="d-flex h-100">
                        <div class="bg-light border-end" style="width: 200px; min-width: 200px;">
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

                        <div class="flex-grow-1 p-4 bg-white">
                            <div class="tab-content" id="v-pills-tabContent">

                                @foreach($languages as $index => $lang)
                                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="v-pills-{{ $lang->code }}">

                                    @php
                                        // 1. Varsayılan (İngilizce) Etiketler
                                        $layouts = [
                                            ["key" => "hero_overlay_bottom", "image" => asset('screens/full_hero_static.png'), "label" => "Overlay Bottom"],
                                            ["key" => "hero_overlay_center", "image" => asset('screens/hero_overlay_bottom.png'), "label" => "Overlay Center"],
                                            ["key" => "hero_overlay_top", "image" => asset('screens/image_top_text_middle.png'), "label" => "Img Top / Txt Mid"],
                                            ["key" => "full_center", "image" => asset('screens/text_only.png'), "label" => "Text Only"],
                                            ["key" => "full_bottom", "image" => asset('screens/text_top_image_bottom.png'), "label" => "Txt Top / Img Btm"],
                                        ];

                                        // 2. Türkçe için Etiketleri Değiştiriyoruz
                                        if($lang->code == 'tr') {
                                            $layouts = [
                                                ["key" => "hero_overlay_bottom", "image" => asset('screens/full_hero_static.png'), "label" => "Resim Üstü (Alt)"],
                                                ["key" => "hero_overlay_center", "image" => asset('screens/hero_overlay_bottom.png'), "label" => "Resim Üstü (Orta)"],
                                                ["key" => "hero_overlay_top", "image" => asset('screens/image_top_text_middle.png'), "label" => "Resim Üst / Metin Orta"],
                                                ["key" => "full_center", "image" => asset('screens/text_only.png'), "label" => "Sadece Metin"],
                                                ["key" => "full_bottom", "image" => asset('screens/text_top_image_bottom.png'), "label" => "Metin Üst / Resim Alt"],
                                            ];
                                        }

                                        // UI Başlıklarını da dile göre ayarlayalım
                                        $labelLayout = $lang->code == 'tr' ? 'Şablon Seçimi' : 'Select Layout Template';
                                        $labelText   = $lang->code == 'tr' ? 'Metin İçeriği' : 'Text Content';
                                        $labelMedia  = $lang->code == 'tr' ? 'Medya / Görsel' : 'Media Settings';
                                    @endphp

                                    <h6 class="fw-bold text-secondary mb-3">📐 {{ $labelLayout }} <small class="text-muted">({{ strtoupper($lang->code) }})</small></h6>

                                    <div class="row g-3 mb-4">
                                        @foreach($layouts as $layout)
                                        <div class="col-6 col-md-4 col-lg-2">
                                            <label class="layout-card d-block p-2 text-center h-100 w-100">
                                                <input type="radio"
                                                       name="content[translations][{{ $lang->code }}][layout_type]"
                                                       value="{{ $layout['key'] }}"
                                                       class="d-none layout-radio"
                                                       data-lang="{{ $lang->code }}">

                                                <img src="{{ $layout['image'] }}" class="img-fluid rounded mb-2" style="max-height: 80px;">
                                                <div class="small fw-bold text-dark" style="font-size: 0.75rem;">{{ $layout['label'] }}</div>
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
    // PHP'den gelen aktif dil kodları
    const languages = @json($languages->pluck('code'));

    // DataTable Init
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
                   // Kaç dilde veri girilmiş diye basit sayaç
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

    // ================== EDIT MODAL AÇMA ==================
    $(document).on("click", ".editBtn", function () {
        let id = $(this).data("id");
        $("#screenForm")[0].reset();

        // Temizlik
        $(".layout-card").removeClass("active");
        $(".img-preview-box").html('<span class="text-muted small">Empty</span>');

        // İlk sekmeyi aç
        const firstTabTriggerEl = document.querySelector('#v-pills-tab button:first-child');
        if(firstTabTriggerEl) {
            bootstrap.Tab.getInstance(firstTabTriggerEl)?.show() || new bootstrap.Tab(firstTabTriggerEl).show();
        }

        $.get(`/mobile-app-settings/screens/get/${id}`, res => {
            $("#screen_id").val(res.id);

            // KONTROL: Eğer translations nesnesi varsa (Controller 'null' ile çağrıldıysa burası çalışır)
            if (res.content && res.content.translations) {

                languages.forEach(code => {
                    let transData = res.content.translations[code];

                    if (transData) {
                        // 1. Layout
                        if(transData.layout_type){
                            let radio = $(`input[name="content[translations][${code}][layout_type]"][value="${transData.layout_type}"]`);
                            radio.prop("checked", true);
                            radio.closest(".layout-card").addClass("active");
                        }

                        // 2. Metinler
                        $(`[name="content[translations][${code}][title]"]`).val(transData.title || '');
                        $(`[name="content[translations][${code}][subtitle]"]`).val(transData.subtitle || '');
                        $(`[name="content[translations][${code}][cta_text]"]`).val(transData.cta_text || '');

                        // 3. Görsel
                        if(transData.hero_image && transData.hero_image.url) {
                            let url = transData.hero_image.url;
                            $(`[name="content[translations][${code}][hero_image][url]"]`).val(url);
                            $(`#preview-${code}`).html(`<img src="${url}">`);
                        }
                    }
                });
            } else {
                // Eğer buraya düşüyorsa Controller'da 'null' parametresi unutulmuş demektir.
                console.warn("Translations objesi bulunamadı! Controller'da getById($id, null) kullandığınızdan emin olun.");
            }

            $('#editModal').modal('show');
        });
    });

    // ================== GÖRSEL EFEKTLER ==================
    // Layout Kartına Tıklayınca
    $(document).on("change", ".layout-radio", function(){
        let langCode = $(this).data("lang"); // Hangi dilde işlem yapılıyor?

        // Sadece o dilin panelindeki kartlardan 'active' sınıfını sil
        $(`#v-pills-${langCode} .layout-card`).removeClass("active");

        // Seçileni aktif yap
        $(this).closest(".layout-card").addClass("active");
    });

    // ================== KAYDETME İŞLEMİ ==================
    $("#saveBtn").click(function() {
        let id = $("#screen_id").val();
        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        let formData = new FormData(document.getElementById("screenForm"));

        $.ajax({
            url: `/mobile-app-settings/screens/update/${id}`,
            type: "POST",
            data: formData,
            processData: false, // Multipart form için gerekli
            contentType: false, // Multipart form için gerekli
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
