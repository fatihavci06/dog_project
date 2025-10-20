@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">

            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" id="list-title">Conversations</h5>
                        <button class="btn btn-sm btn-outline-primary" id="toggle-list" data-mode="conversations">
                            New Chat
                        </button>
                    </div>

                    {{-- User Search Input (New Chat modunda görünür) --}}
                    <div class="p-3 border-bottom d-none" id="user-search-container">
                        <input type="text" id="user-search-input" class="form-control"
                            placeholder="Search users by name...">
                    </div>

                    {{-- Conversation Search Input (Conversations modunda görünür) --}}
                    <div class="p-3 border-bottom" id="conversation-search-container">
                        <input type="text" id="conversation-search-input" class="form-control"
                            placeholder="Search conversations by name...">
                    </div>


                    {{-- Conversation List --}}
                    <div class="card-body" id="conversation-list" style="max-height:80vh;overflow-y:auto;">
                        @forelse($conversations as $conversation)
                            @php
                                $otherUserId =
                                    $conversation->user_one_id == auth()->id()
                                        ? $conversation->user_two_id
                                        : $conversation->user_one_id;
                                // NOTE: Assuming $users is pre-loaded or using the Eloquent relationship correctly here
                                $otherUser = \App\Models\User::find($otherUserId);
                                $lastMessage = $conversation->lastMessage;
                            @endphp
                            {{-- Added 'selected-bg' class for dynamic highlighting --}}
                            <a href="#"
                                class="list-item conversation-item d-flex align-items-center mb-3 p-2 border rounded text-decoration-none text-dark {{ $selectedConversation == $conversation->id ? 'selected-bg' : '' }}"
                                data-id="{{ $conversation->id }}" data-name="{{ $otherUser->name }}">
                                <img class="rounded-circle mr-3" width="50" height="50"
                                    src="{{ $otherUser->profile_photo_url ?? asset('storage/profile.jpg') }}">
                                <div class="flex-fill">
                                    <strong>{{ $otherUser->name }}</strong>
                                    <p class="mb-0 text-truncate">{{ $lastMessage?->body }}</p>
                                    <small class="text-muted">{{ $lastMessage?->created_at?->diffForHumans() }}</small>
                                </div>
                            </a>
                        @empty
                            <p class="text-center text-gray-500" id="no-conversations">No conversations.</p>
                        @endforelse
                    </div>

                    {{-- All Other Users List (Initially hidden) --}}
                    <div class="card-body d-none" id="user-list-search" style="max-height:80vh;overflow-y:auto;">
                        {{-- AJAX ile sayfalandırılmış kullanıcılar buraya yüklenecek --}}
                        @include('messages.partials.user_list_items', ['allOtherUsers' => $allOtherUsers])
                    </div>

                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header" id="chat-title">
                        {{-- Pre-populate title if selected (optional, but good practice) --}}
                        @if ($selectedConversation)
                            @php
                                $selectedItem = $conversations->firstWhere('id', $selectedConversation);
                                $chatPartnerId =
                                    $selectedItem->user_one_id == auth()->id()
                                        ? $selectedItem->user_two_id
                                        : $selectedItem->user_one_id;
                                $chatPartner = \App\Models\User::find($chatPartnerId);
                            @endphp
                            Chat with {{ $chatPartner->name }}
                        @else
                            Select a conversation or start a new chat
                        @endif
                    </div>
                    <div class="card-body" id="chat-messages" style="max-height:70vh;overflow-y:auto;">
                        {{-- If a conversation is selected, the server-side should pre-render messages here --}}
                        @if ($messages->isNotEmpty())
                            {{-- We still rely on JS to load messages, but we can display a loading message if needed --}}
                            <p class="text-center text-muted">Loading messages...</p>
                        @else
                            <p class="text-center text-gray-500">No conversation selected.</p>
                        @endif
                    </div>
                    <div class="card-footer">
                        <form id="chat-form">
                            @csrf
                            {{-- Form Data Inputs --}}
                            <input type="hidden" id="conversation_id" name="conversation_id"
                                value="{{ $selectedConversation ?? '' }}">
                            <input type="hidden" id="recipient_id" name="recipient_id" value="">

                            <div class="input-group">
                                <input type="text" id="message-body" name="body" class="form-control"
                                    placeholder="Type a message..." {{ $selectedConversation ? '' : 'disabled' }}>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"
                                        {{ $selectedConversation ? '' : 'disabled' }}>Send</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <style>
        .list-item:hover {
            background-color: #f7f7f7;
        }

        .selected-bg {
            /* Highlight color for the selected conversation or user */
            background-color: #e9ecef;
            /* Light gray from Bootstrap */
            border: 1px solid #c8d2da !important;
            font-weight: bold;
        }

        /* Bootstrap pagination link rengi fixi */
        #user-pagination-links a.page-link {
            color: #007bff;
            /* Mavi link rengi */
        }
    </style>
    <script>
        $(function() {
            // --- Global Variables and Constants ---
            const initialSelectedId = {{ Js::from($selectedConversation ?? null) }};
            let currentConversation = initialSelectedId;
            let currentRecipient = null;

            // Rota tanımları
            const markReadRouteTemplate = '{{ route('message.markRead', ['id' => '__id__']) }}';
            const messageStoreRouteTemplate = '{{ route('messages.store', ['conversation' => '__id__']) }}';
            const startNewChatRoute = '{{ route('messages.start') }}';
            // YENİ ROTA
            const getOtherUsersRoute = '{{ route('users.getOtherUsers') }}';

            // DOM Elemanları
            const $conversationList = $('#conversation-list');
            const $userListSearch = $('#user-list-search');
            const $toggleButton = $('#toggle-list');

            // Aramalar
            const $userSearchContainer = $('#user-search-container');
            const $userSearchInput = $('#user-search-input');

            const $conversationSearchContainer = $('#conversation-search-container');
            const $conversationSearchInput = $('#conversation-search-input');

            const $chatTitle = $('#chat-title');
            const $messageBody = $('#message-body');
            const $chatForm = $('#chat-form');
            const $chatSendButton = $chatForm.find('button[type="submit"]');

            let searchTimeout; // Debounce için

            // --- State Management Helpers ---

            const setChatPanelState = (isEnabled, title) => {
                $messageBody.prop('disabled', !isEnabled);
                $chatSendButton.prop('disabled', !isEnabled);
                $chatTitle.text(title || 'Select a conversation or start a new chat');
                if (!isEnabled) $('#chat-messages').empty();
            };

            const updateHighlight = (element, conversationId = null, recipientId = null) => {
                $('.list-item').removeClass('selected-bg');
                if (element) {
                    element.addClass('selected-bg');
                }

                $('#conversation_id').val(conversationId || '');
                $('#recipient_id').val(recipientId || '');
            };

            // Fonksiyon: Mevcut Konuşmayı Yükle
            const loadConversation = function(conversationItem) {
                const conversationId = conversationItem.data('id');
                currentConversation = conversationId;
                currentRecipient = null;

                updateHighlight(conversationItem, conversationId, null);
                setChatPanelState(true, `Chat with ${conversationItem.data('name')}`);

                const markReadUrl = markReadRouteTemplate.replace('__id__', conversationId);
                $.post(markReadUrl, {
                    _token: '{{ csrf_token() }}'
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Failed to mark conversation as read:", errorThrown);
                });

                $.get(`/messages/${conversationId}`, function(data) {
                    $('#chat-messages').html(data);
                    const chatMessages = $('#chat-messages');
                    chatMessages.scrollTop(chatMessages[0].scrollHeight);
                });
            }

            // Fonksiyon: Yeni Sohbet Başlat
            const startNewChat = function(userItem) {
                currentRecipient = userItem.data('userId');
                currentConversation = null;

                updateHighlight(userItem, null, currentRecipient);
                setChatPanelState(true, `New chat with ${userItem.data('name')}`);
                $('#chat-messages').html(
                    `<p class="text-center text-muted">Send your first message to start the conversation.</p>`
                );
            }

            // --- YENİ CORE FUNCTIONALITY: AJAX ile Kullanıcı Yükleme ---
            const loadUsers = function(page = 1, query = '') {
                // Loading gösterimi
                $userListSearch.html('<p class="text-center text-muted p-5">Loading users...</p>');

                $.ajax({
                    url: getOtherUsersRoute,
                    type: 'GET',
                    data: {
                        page: page,
                        query: query
                    },
                    success: function(html) {
                        $userListSearch.html(html);
                        setupPaginationLinks(); // Yeni linkleri dinlemeye başla
                    },
                    error: function(xhr) {
                        $userListSearch.html(
                            '<p class="text-center text-danger p-5">Could not load users. Please try again.</p>'
                        );
                        console.error("User load error:", xhr.responseText);
                    }
                });
            };

            // Fonksiyon: Sayfalandırma Linklerini Dinle
            const setupPaginationLinks = function() {
                // Mevcut dinleyicileri kaldır (tekrar tekrar dinlememek için)
                $('#user-pagination-links a.page-link').off('click').on('click', function(e) {
                    e.preventDefault();

                    const url = new URL($(this).attr('href'));
                    const page = url.searchParams.get('page');

                    // Arama kutusundaki mevcut sorguyu al
                    const query = $userSearchInput.val();

                    loadUsers(page, query);
                });
            };


            // --- Event Handlers ---

            // 1. Conversation click handler (Existing chats)
            $conversationList.on('click', '.conversation-item', function(e) {
                e.preventDefault();
                loadConversation($(this));
            });

            // 2. User click handler (New chats)
            $userListSearch.on('click', '.user-item', function(e) {
                e.preventDefault();
                startNewChat($(this));
            });

            // 3. Toggle between Conversations and New Chat modes (GÜNCELLENMİŞ)
            $toggleButton.on('click', function() {
                const mode = $(this).data('mode');

                // Arama kutularını ve listeleri sıfırla/göster
                $userSearchInput.val('');
                $conversationSearchInput.val('');
                $('#user-list-search .user-item').show();
                $('#conversation-list .conversation-item').show();

                if (mode === 'conversations') {
                    // Switch to New Chat mode (User List)
                    $conversationList.addClass('d-none');
                    $conversationSearchContainer.addClass('d-none');

                    $userListSearch.removeClass('d-none');
                    $userSearchContainer.removeClass('d-none');

                    $('#list-title').text('Select User');
                    $(this).text('Conversations').data('mode', 'users');

                    // Yeni sohbet moduna geçildiğinde ilk sayfayı yükle
                    loadUsers(1, '');

                } else {
                    // Switch to Conversations mode (Conversation List)
                    $userListSearch.addClass('d-none');
                    $userSearchContainer.addClass('d-none');

                    $conversationList.removeClass('d-none');
                    $conversationSearchContainer.removeClass('d-none');

                    $('#list-title').text('Conversations');
                    $(this).text('New Chat').data('mode', 'conversations');
                }
                updateHighlight(null);
                setChatPanelState(false);
            });

            // 4. Client-side Conversation Search/Filter (Konuşma İsimlerini Filtreler)
            // 4. Client-side Conversation Search/Filter (Konuşma İsimlerini Filtreler)
            $conversationSearchInput.on('keyup', function() {
                const query = $(this).val().toLowerCase();

                // Console logları şimdilik kaldırıyorum, çalıştığını biliyoruz.

                if ($conversationList.is(':visible')) {
                    $conversationList.find('.conversation-item').each(function() {
                        const $item = $(this);
                        const rawName = $item.data('name');
                        const userName = rawName ? rawName.toLowerCase() : '';

                        if (userName.includes(query)) {
                            // Eşleştiğinde: d-none'u kaldır, d-flex'i ekle (Çünkü varsayılan görünümü flex)
                            $item.removeClass('d-none').addClass('d-flex');
                        } else {
                            // Eşleşmediğinde: d-flex'i kaldır, d-none'u ekle (Gizle)
                            $item.removeClass('d-flex').addClass('d-none');
                        }
                    });
                }
            });


            // 6. Mesaj gönderme (Message sending)
            $chatForm.submit(function(e) {
                e.preventDefault();

                const body = $messageBody.val();
                if (!body.trim()) return;

                let url;
                let postData = $chatForm.serialize();
                let isNewChat = false;

                if (currentConversation) {
                    url = messageStoreRouteTemplate.replace('__id__', currentConversation);
                    isNewChat = false;
                } else if (currentRecipient) {
                    url = startNewChatRoute;
                    isNewChat = true;
                } else {
                    alert("Lütfen bir konuşma veya kullanıcı seçin.");
                    return;
                }


                $.post(url, postData, function(data) {
                    if (data.success) {
                        if (isNewChat) {
                            currentConversation = data.conversation_id;
                            $('#conversation_id').val(currentConversation);

                            const $selectedUserItem = $(
                                `.user-item[data-user-id="${currentRecipient}"]`);
                            const userName = $selectedUserItem.data('name');

                            // Yeni sohbeti UI'da conversation listesine ekle
                            const newConvoItem = $(
                                `<a href="#" class="list-item conversation-item d-flex align-items-center mb-3 p-2 border rounded text-decoration-none text-dark selected-bg" data-id="${currentConversation}" data-name="${userName}">
                                <img class="rounded-circle mr-3" width="50" height="50" src="${$selectedUserItem.find('img').attr('src')}">
                                <div class="flex-fill"><strong>${userName}</strong><p class="mb-0 text-truncate">${body}</p><small class="text-muted">Just now</small></div>
                            </a>`);

                            $('#no-conversations').remove();
                            $conversationList.prepend(newConvoItem);

                            // Listeyi Conversation moduna geri getir ve yeni öğeyi seçili yap
                            $toggleButton.trigger('click');
                            $toggleButton.trigger('click');

                            $(`.conversation-item[data-id="${currentConversation}"]`).addClass(
                                'selected-bg');

                            setChatPanelState(true, `Chat with ${userName}`);
                        }

                        // Mesajı Chat Panel'e ekle
                        $('#chat-messages').append(`
                            <div class="d-flex justify-content-end mb-2">
                                <div class="bg-primary text-white p-2 rounded max-w-75">
                                    ${data.message.body}
                                    <small class="d-block text-right opacity-75">Just now</small>
                                </div>
                            </div>
                        `);

                        // En üstteki sohbetin son mesajını güncelle
                        $(`.conversation-item[data-id="${currentConversation}"]`).find('p.mb-0')
                            .text(
                                data.message.body);


                        $messageBody.val('');
                        $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Mesaj gönderme başarısız:", errorThrown, jqXHR.responseText);

                });
            });

            // 7. Read more (Load more messages)
            $('#chat-messages').on('click', '#load-more', function(e) {
                e.preventDefault();
                const lastId = $(this).data('last-id');
                $.get(`/messages/${currentConversation}/load-more?last_message_id=${lastId}`, function(
                    data) {
                    const chatMessages = $('#chat-messages');
                    const oldHeight = chatMessages[0].scrollHeight;

                    chatMessages.prepend(data);

                    const newHeight = chatMessages.scrollHeight;
                    chatMessages.scrollTop(newHeight - oldHeight);
                });
            });

            // 8. Initial setup
            if (initialSelectedId) {
                const $initialItem = $(`.conversation-item[data-id="${initialSelectedId}"]`);
                if ($initialItem.length) {
                    loadConversation($initialItem);
                }
            }

            // Sayfalandırma linklerini ilk yüklemede dinlemeye başla
            setupPaginationLinks();

        });
    </script>
@endsection
