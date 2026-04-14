@extends('expert.layouts.app')

@section('title', 'Payouts')
@section('page-title', 'Payouts & Earnings')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card card-agri h-100">
            <div class="card-body">
                <h6 class="text-muted mb-2">Stripe Connect</h6>
                @php $connected = ($stripeAccount?->onboarding_status ?? 'pending') === 'completed'; @endphp
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge-agri {{ $connected ? 'badge-success-agri' : 'badge-warning-agri' }}">
                        {{ $connected ? 'Connected' : 'Action Required' }}
                    </span>
                    <a href="{{ route('expert.payouts.connect') }}" class="btn-agri btn-agri-primary">
                        {{ $connected ? 'Update Stripe' : 'Connect Stripe' }}
                    </a>
                </div>
                <p class="text-muted small mt-3 mb-0">Appointment payouts are sent to your connected Stripe account.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-agri h-100">
            <div class="card-body">
                <h6 class="text-muted mb-3">Earnings Summary</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#F9FAFB;">
                            <div class="text-muted small">Gross</div>
                            <div class="fw-bold fs-5">Rs {{ number_format($totals['gross'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#F9FAFB;">
                            <div class="text-muted small">Commission</div>
                            <div class="fw-bold fs-5">Rs {{ number_format($totals['commission'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded" style="background:#F9FAFB;">
                            <div class="text-muted small">Net Paid</div>
                            <div class="fw-bold fs-5">Rs {{ number_format($totals['net'] ?? 0, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-agri">
    <div class="card-body">
        <h5 class="mb-3">Payout History</h5>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Gross</th>
                        <th>Commission</th>
                        <th>Net</th>
                        <th>Status</th>
                        <th>Transfer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr>
                            <td>{{ optional($payout->created_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ ucfirst($payout->payment_type ?? 'appointment') }}</td>
                            <td>Rs {{ number_format((float) $payout->amount, 2) }}</td>
                            <td>Rs {{ number_format((float) $payout->commission, 2) }}</td>
                            <td class="fw-semibold">Rs {{ number_format((float) $payout->net_amount, 2) }}</td>
                            <td>
                                <span class="badge-agri {{ $payout->status === 'paid' ? 'badge-success-agri' : ($payout->status === 'failed' ? 'badge-danger-agri' : 'badge-warning-agri') }}">
                                    {{ ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td>{{ $payout->stripe_transfer_id ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No payouts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $payouts->links() }}
        </div>
    </div>
</div>
@endsection