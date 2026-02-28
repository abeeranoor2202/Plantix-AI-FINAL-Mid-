@extends('expert.layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notification Center')

@section('content')
<div class="card-agri p-0 border-0 shadow-sm bg-white overflow-hidden">
    <div class="p-4 bg-light border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="fas fa-bell me-2 text-primary"></i>Your Notifications
            @if($unreadCount > 0)
                <span class="badge-agri bg-danger text-white ms-2 shadow-sm" style="padding: 0.3em 0.8em; font-size: 11px;">{{ $unreadCount }} New</span>
            @endif
        </h5>
        
        @if($unreadCount > 0)
        <form method="POST" action="{{ route('expert.notifications.read-all') }}" class="m-0">
            @csrf
            <button type="submit" class="btn-agri btn-agri-outline py-1 px-3 fs-6 d-flex align-items-center gap-2">
                <i class="fas fa-check-double text-success"></i> Mark All Read
            </button>
        </form>
        @endif
    </div>

    <div class="list-group list-group-flush pt-1">
        @forelse($notifications->items() as $notif)
        <div class="list-group-item border-bottom-dashed p-4 position-relative" style="{{ !$notif->is_read ? 'background-color: var(--agri-primary-light) !important;' : 'background-color: transparent;' }} border-left: {{ !$notif->is_read ? '4px solid var(--agri-primary)' : '4px solid transparent' }}; transition: background 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='{{ !$notif->is_read ? 'var(--agri-primary-light)' : 'transparent' }}'">
            
            <div class="d-flex flex-column flex-sm-row align-items-start gap-3 gap-sm-4">
                
                @php
                    $iconDetails = match(true) {
                        str_starts_with($notif->type, 'appointment') => ['icon' => 'fa-calendar-check', 'color' => 'success'],
                        str_starts_with($notif->type, 'forum')       => ['icon' => 'fa-comments', 'color' => 'primary'],
                        str_starts_with($notif->type, 'admin')       => ['icon' => 'fa-bullhorn', 'color' => 'warning'],
                        default                                       => ['icon' => 'fa-bell', 'color' => 'info'],
                    };
                @endphp
                
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm
                            {{ !$notif->is_read ? 'bg-'.$iconDetails['color'].' text-white border border-white border-2' : 'bg-light text-'.$iconDetails['color'].' border border-'.$iconDetails['color'].' border-opacity-25' }}"
                     style="width: 48px; height: 48px; font-size: 1.2rem;">
                    <i class="fas {{ $iconDetails['icon'] }}"></i>
                </div>
                
                <div class="flex-grow-1 w-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-1 gap-2">
                        <div class="fw-bold fs-6 pe-3 {{ !$notif->is_read ? 'text-dark' : 'text-secondary' }}" style="line-height: 1.4;">
                            {{ $notif->title }}
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto ms-sm-0">
                            <span class="text-muted text-uppercase fw-bold small" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="far fa-clock me-1 opacity-50"></i>{{ $notif->created_at->diffForHumans() }}
                            </span>
                            @if(!$notif->is_read)
                                <form method="POST" action="{{ route('expert.notifications.read', $notif) }}" class="m-0 ms-1">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-white border shadow-sm rounded-circle d-flex align-items-center justify-content-center text-success"
                                            style="width: 28px; height: 28px; padding: 0;" title="Mark as Read">
                                        <i class="fas fa-check" style="font-size: 12px;"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    @if($notif->body)
                        <p class="mb-2 mt-2 {{ !$notif->is_read ? 'fw-medium text-dark' : 'text-muted' }}" style="line-height: 1.6; font-size: 14px;">
                            {{ $notif->body }}
                        </p>
                    @endif
                    
                    <span class="badge-agri bg-light text-muted border shadow-sm mt-1" style="font-size: 11px; padding: 0.3em 0.8em;">
                        <i class="fas fa-tag me-1 opacity-50"></i>{{ ucfirst(str_replace('_', ' ', $notif->type)) }}
                    </span>
                </div>
            </div>
        </div>
        @empty
        <div class="p-5 text-center my-4 d-flex flex-column align-items-center justify-content-center">
            <div class="bg-light rounded-circle shadow-sm d-flex align-items-center justify-content-center mb-3 border border-dashed text-muted opacity-50" style="width: 90px; height: 90px;">
                <i class="far fa-bell-slash fs-2"></i>
            </div>
            <h4 class="fw-bold text-dark">No Notifications</h4>
            <p class="text-muted small fw-medium mb-0">You're all caught up! Check back later for new alerts.</p>
        </div>
        @endforelse
    </div>
    
    @if($notifications->hasPages())
    <div class="p-4 bg-light border-top text-center">
        {{ $notifications->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
