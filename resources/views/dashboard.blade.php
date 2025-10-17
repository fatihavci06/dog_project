@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<h1 class="h3 mb-4 text-gray-800">Dashboard 📊</h1>

<div class="row mb-4">

    <!-- Satır 1 -->
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-primary shadow">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Active Users 👤</h5>
                    <h3>{{ $activeUsersCount }}</h3>
                </div>
                <div style="font-size: 2.5rem;">🟢</div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-white bg-success shadow">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Dogs🐶</h5>
                    <h3>{{ $activeDogsCount }}</h3>
                </div>
                <div style="font-size: 2.5rem;">🐕</div>
            </div>
        </div>
    </div>

    <!-- Satır 2 -->
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-warning shadow">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Dog Owners 🐾</h5>
                    <h3>{{ $dogOwnersCount }}</h3>
                </div>
                <div style="font-size: 2.5rem;">👨‍👩‍👧‍👦</div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-white bg-info shadow">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title">Adoption Seekers 🐕‍🦺</h5>
                    <h3>{{ $adoptionSeekersCount }}</h3>
                </div>
                <div style="font-size: 2.5rem;">🧑‍🤝‍🧑</div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    // Şimdilik JS gerek yok
</script>
@endsection
