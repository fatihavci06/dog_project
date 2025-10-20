@foreach($messages as $message)
<div class="mb-2">
    <strong>{{ $message->sender->id === auth()->id() ? 'You' : $message->sender->name }}:</strong>
    {{ $message->body }}
    <small class="text-muted">{{ $message->created_at->format('H:i, d M') }}</small>
</div>
@endforeach

@if($messages->count() >= 20)
<div class="text-center my-2">
    <a href="#" id="load-more" data-last-id="{{ $messages->first()->id }}">Read More</a>
</div>
@endif
