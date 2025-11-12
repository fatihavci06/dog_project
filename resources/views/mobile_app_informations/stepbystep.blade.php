@extends('layouts.app')

@section('title', 'Mobile App Information Settings')

@section('content')

    <h2 class="mb-4">Mobile App Information Settings</h2>

    {{-- Session ile gelen başarı mesajı (Sayfa yenilemede kullanılır) --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ✨ EKLENDİ/GÜNCELLENDİ: AJAX ile gelen başarı mesajını göstermek için div --}}
    <div id="ajax-success-alert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
        <strong>Success!</strong> <span id="alert-message-text"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    {{-- ----------------------------------------------------------------- --}}

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Step By Step Information</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Step Number</th>
                            <th>Description</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr data-id="{{ $item->id }}">
                                <td>{{ $item->step_number }}</td>
                                <td>{{ $item->step_description }}</td>
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

    {{-- ✨ GÜNCELLENMİŞ MODAL KISMI ✨ --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                {{-- Adım numarasını gösterecek alan --}}
                <h6 class="modal-title px-3 pt-2 pb-0 text-secondary" id="modalStepNumber"></h6>
                <div class="modal-body">
                    <form id="editForm">
                        @csrf
                        <input type="hidden" id="record_id">

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea id="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- --------------------------------- --}}

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Edit modalını aç
            $('.edit-btn').on('click', function() {
                let tr = $(this).closest('tr');
                let id = tr.data('id');
                let step_number = tr.find('td:eq(0)').text(); // Adım Numarasını al
                let description = tr.find('td:eq(1)').text();

                // Adım Numarasını modalın bilgi satırına yerleştir
                $('#modalStepNumber').text(`Step Number: ${step_number}`);

                $('#record_id').val(id);
                $('#description').val(description);

                $('#editModal').modal('show');
            });

            // AJAX ile güncelle
            $('#editForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#record_id').val();
                let description = $('#description').val();

                $.ajax({
                    url: "{{ route('mobileAppInformation.update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        description: description
                    },
                    success: function(res) {
                        if (res.success) {
                            // 1. Tabloyu güncelle
                            let tr = $('tr[data-id="' + id + '"]');
                            tr.find('td:eq(1)').text(description);

                            // 2. Modalı kapat
                            $('#editModal').modal('hide');
                            $('#modalStepNumber').text(''); // Adım numarasını temizle

                            // 3. ✨ BAŞARI MESAJINI GÖSTER ✨
                            // Controller'dan gelen mesajı veya varsayılan mesajı ayarla
                            $('#alert-message-text').text(res.message || 'The information has been updated successfully.');

                            // Alert kutusunu görünür yap ve 3 saniye sonra kaybolmasını sağla
                            $('#ajax-success-alert').fadeIn().delay(3000).fadeOut();

                        } else {
                            alert(res.message || "Güncelleme sırasında bir hata oluştu!");
                        }
                    },
                    error: function() {
                        alert("Sunucu hatası! Lütfen tekrar deneyin.");
                    }
                });
            });

        });
    </script>
@endsection
