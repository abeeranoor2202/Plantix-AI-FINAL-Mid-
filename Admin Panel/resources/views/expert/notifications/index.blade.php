@extends('expert.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-notification-center-panel
        title="Notifications"
        subtitle="Latest updates and actions"
        :notifications="$notifications"
        :unread-count="$unreadCount"
        :filters="$filters ?? []"
        :index-url="route('expert.notifications.index')"
        :read-all-url="route('expert.notifications.read-all')"
        :clear-all-url="route('expert.notifications.clear-all')"
        :mark-read-url-base="route('expert.notifications.read', ['notification' => 0])"
        :type-options="['appointments' => 'Appointments', 'forum' => 'Forum', 'payments' => 'Payments', 'system' => 'System']"
    />
</div>
@endsection
