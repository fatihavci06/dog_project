@extends('layouts.app')

@section('title', 'User Profile Details')

@section('content')
<h1 class="h2 mb-4 text-primary"><i class="fas fa-user-circle mr-2"></i> User Profile Details</h1>

<div class="row">
    {{-- Basic Info Card --}}
    <div class="col-lg-5 col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @if($user->photo_url)
                        {{-- photo_url kullanıldı --}}
                        <img src="{{ $user->photo_url }}" alt="Profile Photo" class="rounded-circle border p-1" width="120" height="120" style="object-fit: cover;">
                    @else
                        <i class="fas fa-user-circle fa-5x text-secondary"></i>
                    @endif
                </div>

                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong><i class="fas fa-signature mr-2"></i> Name:</strong> {{ $user->name }}</li>
                    <li class="list-group-item"><strong><i class="fas fa-envelope mr-2"></i> Email:</strong> {{ $user->email }}</li>
                    <li class="list-group-item">
                        <strong><i class="fas fa-toggle-on mr-2"></i> Status:</strong>
                        <span class="badge rounded-pill {{ $user->status == 'active' ? 'bg-success' : 'bg-danger' }} p-2">
                            {{ ucfirst($user->status) }}
                        </span>
                    </li>
                    {{-- JSON'daki alan kullanıldı --}}
                    <li class="list-group-item"><strong><i class="far fa-calendar-alt mr-2"></i> Email Verified At:</strong> {{ \Carbon\Carbon::parse($user->email_verified_at)->format('d M Y H:i') }}</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Location & Other Details Card --}}
    <div class="col-lg-7 col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-map-marker-alt mr-2"></i> Location & Other Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>City:</strong> <span class="badge bg-secondary text-white">{{ $user->location_city ?? 'N/A' }}</span></p>
                        <p><strong>District:</strong> <span class="badge bg-secondary text-white">{{ $user->location_district ?? 'N/A' }}</span></p>
                        <p><strong>User ID:</strong> <span class="badge bg-light text-dark border">{{ $user->id }}</span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Role ID (Main):</strong> {{ $user->role_id ?? '-' }}</p>
                    </div>
                </div>

                <h6 class="mt-3 text-info"><i class="fas fa-book-open mr-2"></i> Biography</h6>
                <p class="border p-3 rounded bg-light">
                    {{ $user->biography ?? 'The user has not added a biography yet. 📝' }}
                </p>
            </div>
        </div>
    </div>
</div>

---

{{-- User Role Assignments (Test) Card - View Butonu Eklendi --}}
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-danger text-white">
        <h3 class="m-0 font-weight-bold"><i class="fas fa-users-cog mr-2"></i> Questionnaire</h3>
    </div>
    <div class="card-body">
        @if($user->testUserRoles->isEmpty())
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> No role assignments found for this user.
            </div>
        @else
            <div class="table-responsive">
                {{-- DataTables ID'si: rolesTable --}}
                <table class="table table-bordered table-hover" id="rolesTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Role Name 👤</th>
                            <th>Dog Name 🐶</th>
                            <th>Assigned At 📅</th>
                            <th class="text-center">Actions</th> {{-- BURAYA EKLENDİ --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->testUserRoles as $roleAssignment)
                            <tr>
                                <td>{{ $roleAssignment->id }}</td>
                                <td><strong>{{ $roleAssignment->role->name ?? 'N/A' }}</strong></td>
                                <td>
                                    {{-- dog_id varsa dog name yazacak yoksa - yazsın --}}
                                    {{ $roleAssignment->dog->first()->name ?? '-' }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($roleAssignment->created_at)->format('d-m-Y H:i') }}</td>
                                <td class="text-center"> {{-- BURAYA EKLENDİ --}}
                                    {{-- Görüntüleme Butonu (Varsayımsal rota kullanıldı, kendi rotanızla değiştirin) --}}
                                    <a href="{{route('questionnaire.show',$roleAssignment->id)}}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

---

{{-- User's Furry Friends (Dogs) Card - 5'erli DataTables ile --}}
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-warning text-white">
        <h3 class="m-0 font-weight-bold"><i class="fas fa-paw mr-2"></i> User's Furry Friends (Dogs)</h3>
    </div>
    <div class="card-body">
        @if($user->userDogs->isEmpty())
            <div class="alert alert-info text-center" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i> No dogs found for this user.
            </div>
        @else
            <div class="table-responsive">
                {{-- DataTables ID'si: dogsTable --}}
                <table class="table table-bordered table-hover" id="dogsTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>#ID</th>
                            <th>Name 🐶</th>
                            <th>Gender</th>
                            <th>Age 🎂</th>
                            <th>Photo</th>
                            <th>Food 🥣</th>
                            <th>Health ⚕️</th>
                            <th>Size</th>
                            <th>Added Date</th>
                            {{-- İstenmediği için buraya Actions sütunu eklenmedi --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($user->userDogs as $dog)
                            <tr>
                                <td>{{ $dog->id }}</td>
                                <td><strong>{{ $dog->name }}</strong></td>
                                <td>
                                    @if($dog->gender == 'male')
                                        Male ♂️
                                    @elseif($dog->gender == 'female')
                                        Female ♀️
                                    @else
                                        {{ ucfirst($dog->gender) ?? '-' }}
                                    @endif
                                </td>
                                <td>{{ $dog->age ?? '-' }}</td>
                                <td class="text-center">
                                    @if($dog->photo_url)
                                        {{-- photo_url kullanıldı --}}
                                        <img src="{{ $dog->photo_url }}" alt="{{ $dog->name }} Photo" width="60" height="60" style="object-fit: cover;" class="rounded">
                                    @else
                                        <i class="fas fa-image fa-2x text-muted"></i>
                                    @endif
                                </td>
                                <td>{{ $dog->food ?? '-' }}</td>
                                <td>{{ $dog->health_status ?? '-' }}</td>
                                <td>{{ $dog->size ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($dog->created_at)->format('d-m-Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<a href="{{ route('users') }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left mr-2"></i> Back to User List</a>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Köpekler Tablosu için 5'erli Sayfalama
        $('#dogsTable').DataTable({
            "pageLength": 5, // Varsayılan sayfa uzunluğu 5
            "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "All"] ], // Kullanıcıya sunulacak sayfalama seçenekleri
            "ordering": true,
            "searching": true
        });

        // Role Atamaları Tablosu için 5'erli Sayfalama
        $('#rolesTable').DataTable({
            "pageLength": 5, // Varsayılan sayfa uzunluğu 5
            "lengthMenu": [ [5, 10, 25, 50, -1], [5, 10, 25, 50, "All"] ],
            "ordering": true,
            "searching": true
        });
    });
</script>
@endsection
