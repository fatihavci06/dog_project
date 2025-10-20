@forelse($allOtherUsers as $user)
    <a href="#"
        class="list-item user-item d-flex align-items-center mb-3 p-2 border rounded text-decoration-none text-dark"
        data-user-id="{{ $user->id }}" data-name="{{ $user->name }}">
        <img class="rounded-circle mr-3" width="50" height="50"
            src="{{ $user->profile_photo_url ?? asset('storage/profile.jpg') }}">
        <div class="flex-fill">
            <strong>{{ $user->name }}</strong>
            <p class="mb-0 text-muted">Start new chat</p>
        </div>
    </a>
@empty
    <p class="text-center text-gray-500" id="no-other-users">No other users found.</p>
@endforelse

{{-- Sayfalandırma Linkleri --}}
<div class="mt-3" id="user-pagination-links">
    {{ $allOtherUsers->links('pagination::bootstrap-4') }}
</div>
