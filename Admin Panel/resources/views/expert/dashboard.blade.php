@extends('expert.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-platform.dashboard-shell
        title="Expert Dashboard"
        subtitle="Appointments, ratings, forum engagement, and review queue"
        :summary-cards="$unifiedSummary ?? []"
        :recent-activity="$unifiedRecentActivity ?? []"
        :pending-actions="$unifiedPendingActions ?? []"
    />
</div>
@endsection
