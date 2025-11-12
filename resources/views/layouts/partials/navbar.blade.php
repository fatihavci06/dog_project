<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Topbar Search -->

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..."
                            aria-label="Search" aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Nav Item - Alerts -->


        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                @if ($unreadCount > 0)
                    <span class="badge badge-danger badge-counter"
      @if ($unreadCount <= 0) style="display:none;" @endif>
    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
</span>

                @endif

            </a>
            <!-- Dropdown - Messages -->
            <!-- Message Center -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">Message Center</h6>

                @forelse($messages as $message)
                    @php
                        $conversationId = $message->conversation_id;
                    @endphp
                    <a class="dropdown-item d-flex align-items-center"
                        href="{{ route('messages.index', ['selected' => $conversationId]) }}">
                        <div class="dropdown-list-image mr-3">
                            <img class="rounded-circle"
                                src="{{ $message->sender->profile_photo_url ?? asset('storage/profile.jpg') }}"
                                alt="{{ $message->sender->name }}">
                            <div class="status-indicator bg-success"></div>
                        </div>
                        <div class="font-weight-bold">
                            <div class="text-truncate">{{ Str::limit($message->body, 60) }}</div>
                            <div class="small text-gray-500">
                                {{ $message->sender->name }} · {{ $message->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </a>
                @empty
                    <span class="dropdown-item text-center small text-gray-500">No new messages</span>
                @endforelse


                <a class="dropdown-item text-center small text-gray-500" href="{{ route('messages.index') }}">
                    Read More Messages
                </a>
            </div>

        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">{{ auth()->user()->name }}</span>
                <img class="img-profile rounded-circle"
                    src="{{ auth()->user()->profile_photo_url ?? asset('storage/profile.jpg') }}">
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <div class="dropdown-divider"></div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
                <a class="dropdown-item" href="#"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const badge = document.querySelector("#messagesDropdown .badge-counter");
    const dropdown = document.querySelector('[aria-labelledby="messagesDropdown"]');

    async function fetchMessages() {
        try {
            const response = await fetch("{{ route('messages.latest') }}");
            const data = await response.json();

            if (!data) return;

            // Badge varsa güncelle
            if (badge) {
                if (data.unreadCount > 0) {
                    badge.textContent = data.unreadCount > 99 ? '99+' : data.unreadCount;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Dropdown varsa güncelle
            if (dropdown) {
                let html = `<h6 class="dropdown-header">Message Center</h6>`;

                if (data.messages.length > 0) {
                    data.messages.forEach(message => {
                        const body = message.body.length > 60 ? message.body.substring(0, 60) + '...' : message.body;
                        const photo = message.sender?.profile_photo_url ?? "{{ asset('storage/profile.jpg') }}";
                        const name = message.sender?.name ?? 'Unknown';
                        const time = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        html += `
                            <a class="dropdown-item d-flex align-items-center"
                                href="/messages?selected=${message.conversation_id}">
                                <div class="dropdown-list-image mr-3">
                                    <img class="rounded-circle" src="${photo}" alt="${name}">
                                    <div class="status-indicator bg-success"></div>
                                </div>
                                <div class="font-weight-bold">
                                    <div class="text-truncate">${body}</div>
                                    <div class="small text-gray-500">${name} · ${time}</div>
                                </div>
                            </a>`;
                    });
                } else {
                    html += `<span class="dropdown-item text-center small text-gray-500">No new messages</span>`;
                }

                html += `<a class="dropdown-item text-center small text-gray-500" href="{{ route('messages.index') }}">
                            Read More Messages
                         </a>`;

                dropdown.innerHTML = html;
            }
        } catch (error) {
            console.error('Mesajlar alınamadı:', error);
        }
    }

    // İlk yüklemede çağır
    fetchMessages();

    // Her 10 saniyede bir yenile
    setInterval(fetchMessages, 600000);
});
</script>

