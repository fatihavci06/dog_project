@extends('layouts.app')

@section('title', 'Looking For List')
@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div id="ajax-success-alert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
        <strong>Success!</strong> <span id="alert-message-text"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Looking For List</h6>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Add New</button>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search..."
                        value="{{ $search ?? '' }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>

                            <th>Name</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $d)
                            <tr id="d-row-{{ $d->id }}">

                                <td>{{ $d->name }}</td>
                                <td>
                                    <button class="btn btn-sm btn-warning editBtn" data-id="{{ $d->id }}"
                                        data-name="{{ $d->name }}" data-bs-toggle="modal"
                                        data-bs-target="#editModal">Edit</button>
                                    <button class="btn btn-sm btn-danger deleteBtn"
                                        data-id="{{ $d->id }}">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="addForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Looking For</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" class="form-control" placeholder="Looking For " required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="editForm" class="modal-content">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Looking For</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ADD
    document.getElementById('addForm').addEventListener('submit', function(e){
        e.preventDefault();
        let formData = new FormData(this);
        fetch('{{ route('lookingFor.store') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                Swal.fire({icon:'success', title:'Added!', text:data.message, timer:1500, showConfirmButton:false})
                .then(()=>location.reload());
            } else {
                Swal.fire({icon:'error', title:'Error', text:data.message || 'Something went wrong!'});
            }
        })
        .catch(err=>{
            Swal.fire({icon:'error', title:'Error', text:'Network or server error!'});
        });
    });

    // EDIT modal fill
    document.querySelectorAll('.editBtn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_name').value = btn.dataset.name;
        });
    });

    // UPDATE
    document.getElementById('editForm').addEventListener('submit', function(e){
        e.preventDefault();
        let id = document.getElementById('edit_id').value;
        let formData = new FormData(this);
        fetch(`/looking-for/update/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: formData
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                Swal.fire({icon:'success', title:'Updated!', text:data.message, timer:1500, showConfirmButton:false})
                .then(()=>location.reload());
            } else {
                Swal.fire({icon:'error', title:'Error', text:data.message || 'Something went wrong!'});
            }
        })
        .catch(err=>{
            Swal.fire({icon:'error', title:'Error', text:'Network or server error!'});
        });
    });

    // DELETE
    document.querySelectorAll('.deleteBtn').forEach(btn=>{
        btn.addEventListener('click', ()=>{
            let id = btn.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the looking for permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(result=>{
                if(result.isConfirmed){
                    fetch(`/looking-for/delete/${id}`, {
                        method:'POST',
                        headers:{
                            'X-CSRF-TOKEN':'{{ csrf_token() }}',
                            'X-HTTP-Method-Override':'DELETE'
                        }
                    })
                    .then(res=>res.json())
                    .then(data=>{
                        if(data.success){
                            Swal.fire({icon:'success', title:'Deleted!', text:data.message, timer:1500, showConfirmButton:false});
                            let row = document.getElementById('d-row-'+id);
                            if(row) row.remove();
                        } else {
                            Swal.fire({icon:'error', title:'Error', text:data.message || 'Something went wrong!'});
                        }
                    })
                    .catch(err=>{
                        Swal.fire({icon:'error', title:'Error', text:'Network or server error!'});
                    });
                }
            });
        });
    });

});
</script>
@endsection


