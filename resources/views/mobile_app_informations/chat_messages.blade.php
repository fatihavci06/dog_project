@extends('layouts.app')

@section('title', 'Chat Ready Messages')

@section('content')

{{-- AJAX İSTEKLERİ İÇİN CSRF TOKEN (Eğer layout dosyanın <head> kısmında yoksa diye buraya ekliyoruz) --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- BAŞARI MESAJI (SWEETALERT) --}}
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

{{-- VALIDASYON HATALARI (SWEETALERT) --}}
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4>Chat Ready Messages</h4>
            <small class="text-muted">Manage and reorder preset questions/messages for the mobile chat app.</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            + Add New
        </button>
    </div>

    <div class="card-body">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th width="50" class="text-center"><i class="fas fa-arrows-alt"></i></th>
                    <th>Type</th>
                    @foreach($languages as $lang)
                        <th>Content ({{ strtoupper($lang->code) }})</th>
                    @endforeach
                    <th width="150">Actions</th>
                </tr>
            </thead>
            {{-- SÜRÜKLENEBİLİR ALAN BAŞLANGICI --}}
            <tbody id="sortable-list">
                @foreach($items as $item)
                    <tr data-id="{{ $item->id }}">
                        <td class="text-center">
                            {{-- SÜRÜKLEME TUTAMACI (HANDLE) --}}
                            <span class="handle" style="cursor: grab; font-size: 1.2rem; color: #6c757d;">&#10021;</span>
                        </td>
                        <td>
                            @if($item->type === 'question')
                                <span class="badge bg-info text-dark">Question</span>
                            @else
                                <span class="badge bg-warning">Message</span>
                            @endif
                        </td>

                        @foreach($languages as $lang)
                            <td>{{ $item->translate('content', $lang->code) }}</td>
                        @endforeach

                        <td>
                            {{-- DÜZENLE BUTONU --}}
                            <button class="btn btn-warning btn-sm editBtn"
                                data-id="{{ $item->id }}"
                                data-type="{{ $item->type }}"
                                @foreach($languages as $lang)
                                    data-content{{ $lang->code }}="{{ $item->translate('content', $lang->code) }}"
                                @endforeach
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
                                Edit
                            </button>

                            {{-- SİLME FORMU VE BUTONU --}}
                            <form action="{{ route('chatMessages.destroy', $item->id) }}" method="POST" class="d-inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger btn-sm deleteBtn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- YENİ EKLEME MODALI --}}
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('chatMessages.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5>Add New Chat Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Type</label>
                <select name="type" class="form-select mb-3" required>
                    <option value="question">Question</option>
                    <option value="message">Message</option>
                </select>

                @foreach($languages as $lang)
                    <div class="mb-3">
                        <label>Content ({{ strtoupper($lang->code) }})</label>
                        <textarea name="content[{{ $lang->code }}]" rows="2" class="form-control" required></textarea>
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- DÜZENLEME MODALI --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5>Edit Chat Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Type</label>
                <select name="type" id="edit_type" class="form-select mb-3" required>
                    <option value="question">Question</option>
                    <option value="message">Message</option>
                </select>

                @foreach($languages as $lang)
                    <div class="mb-3">
                        <label>Content ({{ strtoupper($lang->code) }})</label>
                        <textarea name="content[{{ $lang->code }}]" id="edit_content_{{ $lang->code }}" rows="2" class="form-control" required></textarea>
                    </div>
                @endforeach
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
{{-- KÜTÜPHANELER --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. EDIT MODAL VERİ DOLDURMA İŞLEMİ
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            let id = btn.dataset.id;

            // Form action yolunu kendi yapına göre düzenleyebilirsin
            document.getElementById('editForm').action = "/mobile-app-information/chat-messages/" + id;
            document.getElementById('edit_type').value = btn.dataset.type;

            @foreach($languages as $lang)
                document.getElementById('edit_content_{{ $lang->code }}').value = btn.dataset["content{{ $lang->code }}"] ?? '';
            @endforeach
        });
    });

    // 2. SİLME İŞLEMİ ONAYI (SWEETALERT)
    document.querySelectorAll('.deleteBtn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            let form = this.closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });

    // 3. SÜRÜKLE BIRAK (DRAG & DROP) İLE SIRALAMA İŞLEMİ
    const sortableList = document.getElementById('sortable-list');
    if(sortableList) {
        new Sortable(sortableList, {
            handle: '.handle', // Sadece "handle" class'ına sahip ikondan sürüklenebilir
            animation: 150,
            ghostClass: 'bg-light', // Sürüklenen elemanın arkaplan rengi
            onEnd: function (evt) {
                // Sürükleme bittiğinde yeni sıralamayı diziye al
                let orderArray = [];
                document.querySelectorAll('#sortable-list tr').forEach(function(row) {
                    orderArray.push(row.getAttribute('data-id'));
                });

                // CSRF Token'ı al
                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // AJAX ile yeni sıralamayı sunucuya gönder
                fetch("{{ route('chatMessages.reorder') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                    body: JSON.stringify({ orders: orderArray })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Sıralama başarılıysa ufak bir SweetAlert Toast mesajı göster
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        Toast.fire({ icon: 'success', title: 'Order updated successfully' });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'An error occurred while updating the order.', 'error');
                });
            }
        });
    }
});
</script>
@endsection
