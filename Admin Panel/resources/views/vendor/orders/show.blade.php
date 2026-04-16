@extends('vendor.layouts.app')
@section('title', 'Order #' . $order->id)

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; gap: 12px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('vendor.orders.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Orders</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Order #{{ $order->id }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Order #{{ $order->id }}</h1>
                @php
                    $statusVariant = match($order->status) {
                        'pending' => 'warning',
                        'accepted', 'preparing', 'ready' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    };
                @endphp
                <x-badge :variant="$statusVariant">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</x-badge>
            </div>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
        </div>
        <x-button type="button" variant="outline" icon="fas fa-print" onclick="window.print()">Print Invoice</x-button>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <x-card style="padding: 0; overflow: hidden;">
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Items Purchased</h4>
                </x-slot>
                <x-table>
                    <thead style="background: var(--agri-bg);">
                        <tr>
                            <th style="padding: 14px 24px; font-size: 12px; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Product</th>
                            <th class="text-center" style="padding: 14px 24px; font-size: 12px; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Qty</th>
                            <th class="text-end" style="padding: 14px 24px; font-size: 12px; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Unit Price</th>
                            <th class="text-end" style="padding: 14px 24px; font-size: 12px; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td style="padding: 14px 24px;">
                                    <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $item->product->name ?? $item->name }}</div>
                                    @if($item->variant)
                                        <small class="text-muted">{{ $item->variant }}</small>
                                    @endif
                                </td>
                                <td class="text-center" style="padding: 14px 24px;">{{ $item->quantity }}</td>
                                <td class="text-end" style="padding: 14px 24px;">{{ config('plantix.currency_symbol') }}{{ number_format($item->price, 2) }}</td>
                                <td class="text-end" style="padding: 14px 24px; font-weight: 700;">{{ config('plantix.currency_symbol') }}{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #fff; border-top: 1px solid var(--agri-border);">
                        <tr>
                            <td colspan="3" class="text-end" style="padding: 14px 24px; font-weight: 700; color: var(--agri-text-muted);">Subtotal</td>
                            <td class="text-end" style="padding: 14px 24px; font-weight: 700;">{{ config('plantix.currency_symbol') }}{{ number_format($order->sub_total ?? $order->total, 2) }}</td>
                        </tr>
                        @if($order->coupon_discount)
                            <tr>
                                <td colspan="3" class="text-end" style="padding: 8px 24px; font-weight: 700; color: var(--agri-success);">Coupon Discount</td>
                                <td class="text-end" style="padding: 8px 24px; font-weight: 700; color: var(--agri-success);">-{{ config('plantix.currency_symbol') }}{{ number_format($order->coupon_discount, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end" style="padding: 14px 24px; font-size: 16px; font-weight: 800; color: var(--agri-primary-dark);">Grand Total</td>
                            <td class="text-end" style="padding: 14px 24px; font-size: 18px; font-weight: 900; color: var(--agri-primary-dark);">{{ config('plantix.currency_symbol') }}{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </x-table>
            </x-card>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <x-card>
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Customer Info</h4>
                </x-slot>
                <div style="padding: 18px;">
                    <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $order->user->name ?? 'N/A' }}</div>
                    <div class="text-muted small" style="margin-top: 4px;">{{ $order->user->email ?? 'No email provided' }}</div>
                    @if($order->user->phone)
                        <div class="text-muted small" style="margin-top: 8px;">{{ $order->user->phone }}</div>
                    @endif
                    @if($order->delivery_address)
                        <div style="margin-top: 12px; padding: 10px 12px; background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 10px; color: var(--agri-text-main); font-size: 13px;">
                            {{ $order->delivery_address }}
                        </div>
                    @endif
                </div>
            </x-card>

            @php $nextStatuses = \App\Models\Order::allowedTransitions()[$order->status] ?? []; @endphp
            @if(count($nextStatuses) > 0)
            <x-card>
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Update Status</h4>
                </x-slot>
                <div style="padding: 18px;">
                    <form method="POST" action="{{ route('vendor.orders.status', $order->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="agri-label">Order Progress</label>
                            <select name="status" class="form-agri" required>
                                @foreach($nextStatuses as $s)
                                    <option value="{{ $s }}">{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="agri-label">Additional Note (Optional)</label>
                            <textarea name="notes" rows="3" class="form-agri" placeholder="Message to customer regarding this update..."></textarea>
                        </div>

                        <x-button type="submit" variant="primary" icon="fas fa-check" style="width: 100%;">Apply Status Update</x-button>
                    </form>
                </div>
            </x-card>
            @endif

            <x-card>
                <x-slot name="header">
                    <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Status Timeline</h4>
                </x-slot>
                <div style="padding: 18px;">
                    @forelse($order->statusHistory as $history)
                        <div style="padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid var(--agri-border);">
                            <div style="font-weight: 700; color: var(--agri-text-heading);">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</div>
                            @if($history->notes)
                                <div class="small text-muted" style="margin-top: 2px;">{{ $history->notes }}</div>
                            @endif
                            <div class="small text-muted" style="margin-top: 4px;">
                                {{ $history->created_at?->format('M d, Y h:i A') }}
                                @if($history->changedBy)
                                    • by {{ $history->changedBy->name }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted small">No status history available yet.</div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
