@extends('vendor.layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <x-platform.dashboard-shell
        title="Vendor Dashboard"
        subtitle="Orders, inventory, revenue, and operational alerts"
        :summary-cards="$unifiedSummary ?? []"
        :recent-activity="$unifiedRecentActivity ?? []"
        :pending-actions="$unifiedPendingActions ?? []"
    />
</div>
@endsection
