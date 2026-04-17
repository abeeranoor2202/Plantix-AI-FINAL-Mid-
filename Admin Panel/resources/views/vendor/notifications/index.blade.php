@extends('vendor.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-notification-center-panel
        title="Notifications"
        subtitle="Latest updates and actions"
        :notifications="$notifications"
        :unread-count="$unreadCount"
        :filters="$filters ?? []"
        :index-url="route('vendor.notifications.index')"
        :read-all-url="route('vendor.notifications.read-all')"
        :clear-all-url="route('vendor.notifications.clear-all')"
        :mark-read-url-base="route('vendor.notifications.read', ['id' => 0])"
        :type-options="['order' => 'Order', 'system' => 'System', 'forum' => 'Forum', 'appointment' => 'Appointment']"
    />
</div>
@endsection
