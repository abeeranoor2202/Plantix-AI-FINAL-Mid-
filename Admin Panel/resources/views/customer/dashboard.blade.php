@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<section class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <x-platform.dashboard-shell
            title="Customer Dashboard"
            subtitle="Orders, appointments, forum activity, and notifications in one place"
            :summary-cards="$summaryCards"
            :recent-activity="$recentActivity"
            :pending-actions="$pendingActions"
            activity-title="Recent Activity"
            pending-title="Pending Actions"
            activity-empty-text="No customer activity found."
            pending-empty-text="No customer actions pending."
        />
    </div>
</section>
@endsection