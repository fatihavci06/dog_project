<h2>Yeni Feedback Alındı</h2>

<p><strong>Kategori:</strong> {{ $feedback->category }}</p>
<p><strong>Başlık:</strong> {{ $feedback->subject }}</p>

<p><strong>Mesaj:</strong></p>
<p>{{ $feedback->message }}</p>

@if($feedback->priority)
<p><strong>Öncelik:</strong> {{ strtoupper($feedback->priority) }}</p>
@endif

@if($feedback->rating)
<p><strong>Puan:</strong> {{ $feedback->rating }}/5</p>
@endif

<p><strong>Kullanıcı ID:</strong> {{ $feedback->user_id }}</p>
<p><strong>Tarih:</strong> {{ $feedback->created_at }}</p>

{{-- 🔥 FOTOĞRAF --}}
@if($feedback->image)
<hr>
<p><strong>Eklenen Görsel:</strong></p>

<a href="{{ $feedback->image }}" target="_blank">
    <img
        src="{{ $feedback->image }}"
        alt="Feedback Image"
        style="
            max-width: 400px;
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-top: 8px;
        "
    >
</a>

<p style="font-size:12px;color:#666;">
    Görseli tam boy görmek için tıklayın.
</p>
@endif

@if($feedback->allow_contact)
<hr>
<p><strong>İletişim İzni Var</strong></p>
<p>{{ $feedback->contact_full_name }}</p>
<p>{{ $feedback->contact_email }}</p>
@endif
