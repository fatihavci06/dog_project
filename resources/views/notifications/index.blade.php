@extends('layouts.app')

@section('title', 'Sent Notifications')

@section('content')

    <h2 class="mb-4">Sent Notifications 📬</h2>

    @if ($notifications->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> No notifications have been sent yet.
        </div>
    @else
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Recipients</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $notification)
                            <tr>
                                <td class="fw-bold">{{ $notification->title }}</td>
                                <td>{{ Str::limit($notification->message, 50) }}</td>
                                <td>
                                    @foreach ($notification->users as $user)
                                        <span class="badge bg-info text-dark">{{ $user->name }}</span>
                                    @endforeach
                                </td>

                                <td> {{ $notification->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif

@endsection
