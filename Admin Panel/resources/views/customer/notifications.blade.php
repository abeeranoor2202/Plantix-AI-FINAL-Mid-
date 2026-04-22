@extends('layouts.dashboard')

@section('title', 'Notifications — Plantix AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <x-notification-center-panel
            title="Notifications"
            subtitle="Latest updates and actions"
            :notifications="$notifications"
            :unread-count="$unreadCount"
            :filters="$filters ?? []"
            :index-url="route('notifications.index')"
            :read-all-url="route('notifications.read-all')"
            :clear-all-url="route('notifications.clear-all')"
            :mark-read-url-base="route('notifications.read', ['notification' => 0])"
            :type-options="['order' => 'Order', 'system' => 'System', 'forum' => 'Forum', 'appointment' => 'Appointment']"
        />
    </div>
</div>
@endsection
