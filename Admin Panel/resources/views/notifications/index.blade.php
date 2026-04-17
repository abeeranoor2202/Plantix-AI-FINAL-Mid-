@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
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
@endsection
