@extends('layouts.app')

@section('title', 'Mobile App Page Information List')

@section('content')

    <h2 class="mb-4">Mobile App Page Information List</h2>

    {{-- Session ile gelen başarı mesajı (Sayfa yenilemede kullanılır) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✨ AJAX ile gelen başarı mesajını göstermek için div --}}
    <div id="ajax-success-alert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
        <strong>Success!</strong> <span id="alert-message-text"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    {{-- --------------------------------------------------------- --}}

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Page Info List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Page Name</th>
                            <th>Title</th>
                            <th>Description Preview</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- pageInfo, Controller'dan compact ile gelen değişken adıdır --}}
                        @foreach ($pageInfo as $page)
                            <tr data-id="{{ $page->id }}"
                                data-title="{{ $page->title }}"
                                data-description="{{ $page->description }}"
                                data-image-path="{{ $page->image_path }}">

                                <td>{{ $page->page_name }}</td>
                                <td>{{ $page->title }}</td>
                                <td>{{ Str::limit($page->description, 50) }}</td>
                                <td>
                                    @if ($page->image_path)
                                        <img src="{{ asset('storage/' . $page->image_path) }}" alt="{{ $page->page_name }}" style="max-width: 50px; max-height: 50px;">
                                    @else
                                        No Image
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ✨ DÜZENLEME MODALI (Modal Edit) ✨ --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Page Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- Düzenlenen sayfa adını gösterecek alan --}}
                <h6 class="modal-title px-3 pt-2 pb-0 text-secondary" id="modalPageName"></h6>
                <div class="modal-body">
                    {{-- Form, dosya yükleme için FormData gerektirecek --}}
                    <form id="editForm">
                        @csrf
                        <input type="hidden" name="id" id="record_id">

                        {{-- Başlık --}}
                        <div class="mb-3">
                            <label for="modal_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="modal_title" name="title" required>
                        </div>

                        {{-- Açıklama --}}
                        <div class="mb-3">
                            <label for="modal_description" class="form-label">Description</label>
                            <textarea class="form-control" id="modal_description" name="description" rows="4" required></textarea>
                        </div>

                        {{-- Mevcut Resim ve Yükleme --}}
                        <div class="mb-3">
                            <label class="form-label d-block">Image</label>
                            <div id="current-image-container" class="mb-2">
                                {{-- Resim buraya JavaScript ile yüklenecek --}}
                            </div>
                            <label for="image_file" class="form-label">New Image Upload</label>
                            <input type="file" class="form-control" id="image_file" name="image_file" accept="image/*">
                            <small class="form-text text-muted">Leave blank to keep the current image.</small>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- ------------------------------------------ --}}

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Global değişkeni ayarla
            const STORAGE_PATH = '{{ asset('storage') }}';

            // Edit modalını aç
            $('.edit-btn').on('click', function() {
                let tr = $(this).closest('tr');

                // data- attribute'lerinden verileri al
                let id = tr.data('id');
                let pageName = tr.find('td:eq(0)').text(); // Page Name
                let title = tr.data('title');
                let description = tr.data('description');
                let imagePath = tr.data('image-path');

                // Modala verileri yerleştir
                $('#record_id').val(id);
                $('#modalPageName').text(`Editing: ${pageName}`);
                $('#modal_title').val(title);
                $('#modal_description').val(description);

                // Mevcut resmi göster
                let imageHtml = '';
                if (imagePath) {
                    imageHtml = `<img src="${STORAGE_PATH}/${imagePath}" alt="${pageName}" style="max-width: 150px; height: auto; border: 1px solid #ccc;">`;
                } else {
                    imageHtml = `<div class="text-muted small">No image currently set.</div>`;
                }
                $('#current-image-container').html(imageHtml);

                // Modalı göster
                $('#editModal').modal('show');
            });

            // AJAX ile güncelle
            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                // Dosya ve metin verilerini FormData olarak al
                let formData = new FormData(this);

                // Alert'i gizle (önceki hatalar varsa)
                $('#ajax-success-alert').hide();

                // Güncelleme butonu disable edilirken kullanıcıya geri bildirim ver
                let submitButton = $(this).find('button[type="submit"]');
                submitButton.attr('disabled', true).text('Saving...');

                $.ajax({
                    url: "{{ route('mobileAppInformation.pageInfoUpdate') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        submitButton.attr('disabled', false).text('Save Changes'); // Butonu geri aç

                        if (res.success) {

                            // 1. Modalı kapat
                            $('#editModal').modal('hide');

                            // 2. ✨ BAŞARI MESAJINI GÖSTERME KISMI ✨
                            $('#alert-message-text').text(res.message || 'Page information updated successfully.');
                            $('#ajax-success-alert').fadeIn().delay(3000).fadeOut();

                            // 3. Tabloyu güncelle (Resim değiştiyse yenileme en güvenli yoldur)
                            setTimeout(function() {
                                window.location.reload();
                            }, 500);

                        } else {
                            alert(res.message || "Güncelleme sırasında bir hata oluştu!");
                        }
                    },
                    error: function(xhr) {
                        submitButton.attr('disabled', false).text('Save Changes'); // Butonu geri aç
                        let errorMessage = "Sunucu hatası! Lütfen tekrar deneyin.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422) {
                            // Basit bir validasyon hatası bildirimi
                             errorMessage = "Validation Error! Check your inputs.";
                        }
                        alert(errorMessage);
                    }
                });
            });

        });
    </script>
@endsection
