@extends('layouts.frontend')

@section('title', 'Notifications — Plantix AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')
<div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <h1>Notifications</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
                        <li class="active">Notifications</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="default-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold mb-0">
                        Your Notifications
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-2">{{ $unreadCount }} new</span>
                        @endif
                    </h4>
                    @if($notifications->total() > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-check2-all me-1"></i>Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>

                @forelse($notifications as $note)
                    <div class="card mb-2 border-0 shadow-sm {{ $note->read ? '' : 'border-start border-success border-3' }}">
                        <div class="card-body d-flex gap-3 align-items-start">
                            <div class="flex-shrink-0 pt-1">
                                @php
                                    $icon = match($note->type ?? '') {
                                        'weather'     => 'bi-cloud-sun text-info',
                                        'order'       => 'bi-bag-check text-primary',
                                        'appointment' => 'bi-calendar-check text-warning',
                                        'disease'     => 'bi-bug text-danger',
                                        default       => 'bi-bell text-success',
                                    };
                                @endphp
                                <i class="bi {{ $icon }} fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-0 fw-semibold {{ $note->read ? 'text-secondary' : '' }}">{{ $note->title }}</p>
                                <p class="mb-1 small text-muted">{{ $note->message }}</p>
                                <p class="mb-0 text-muted" style="font-size:.75rem;">
                                    {{ $note->sent_at ? $note->sent_at->diffForHumans() : $note->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if(!$note->read)
                                <form method="POST" action="{{ route('notifications.read', $note->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary"
                                            title="Mark as read">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash fs-1 text-muted"></i>
                        <p class="text-muted mt-3">You have no notifications yet.</p>
                    </div>
                @endforelse

                <div class="d-flex justify-content-center mt-4">
                    {{ $notifications->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
