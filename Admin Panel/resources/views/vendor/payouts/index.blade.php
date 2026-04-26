@extends('vendor.layouts.app')

@section('title', 'Payouts')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Payouts & Earnings</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Track payout history and Stripe connection status.</p>
        </div>
    </div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card-agri h-100">
            <div class="card-body">
                <h6 class="text-muted mb-2">Stripe Connect</h6>
                @php $connected = ($stripeAccount?->onboarding_status ?? 'pending') === 'completed'; @endphp
                <div class="d-flex align-items-center justify-content-between">
                    <x-badge :variant="$connected ? 'success' : 'warning'">{{ $connected ? 'Connected' : 'Action Required' }}</x-badge>
                </div>
                <p class="text-muted small mt-3 mb-0">Payouts can only be sent after Stripe onboarding is completed.</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-agri h-100">
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

<x-card>
    <x-slot name="header">
        <h4 class="mb-0 fw-bold text-dark" style="font-size:18px;">Payout History</h4>
    </x-slot>
    <div style="padding: 0; overflow: hidden;">
    <div class="card-body">
        <x-table>
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
                            <td>{{ ucfirst($payout->payment_type ?? 'product') }}</td>
                            <td>Rs {{ number_format((float) $payout->amount, 2) }}</td>
                            <td>Rs {{ number_format((float) $payout->commission, 2) }}</td>
                            <td class="fw-semibold">Rs {{ number_format((float) $payout->net_amount, 2) }}</td>
                            <td>
                                <x-badge :variant="$payout->status === 'paid' ? 'success' : ($payout->status === 'failed' ? 'danger' : 'warning')">{{ ucfirst($payout->status) }}</x-badge>
                            </td>
                            <td>{{ $payout->stripe_transfer_id ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No payouts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
        </x-table>

        <div class="mt-3">
            {{ $payouts->links() }}
        </div>
    </div>
</div>
</x-card>
</div>
@endsection