@extends('layouts.frontend')

@section('title', 'Invoice #{{ $order->order_number }}')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
<script>
    window.addEventListener('load', function () {
        // Auto-trigger browser print dialog which lets users save as PDF
        setTimeout(function () { window.print(); }, 500);
    });
</script>
@endsection

@push('styles')
<style>
    @media print {
        .site-header, .site-footer, nav, .breadcrumb-area,
        .no-print, .btn, footer { display: none !important; }
        body { background: #fff; }
        .invoice-wrapper { padding: 0; }
    }
    .invoice-wrapper { max-width: 820px; margin: 40px auto; padding: 0 16px; }
    .invoice-header { border-bottom: 3px solid #2e7d32; padding-bottom: 1rem; margin-bottom: 1.5rem; }
    .invoice-table th { background: #1b5e20; color: #fff; }
    .badge-status { display: inline-block; padding: 4px 10px; border-radius: 20px;
                    font-size: .75rem; font-weight: 600; text-transform: uppercase; }
</style>
@endpush

@section('content')
<div class="invoice-wrapper">

    {{-- Print / Back controls --}}
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <a href="{{ route('order.details', $order->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back to Order
        </a>
        <button onclick="window.print()" class="btn btn-success btn-sm">
            <i class="bi bi-printer me-1"></i>Print / Save as PDF
        </button>
    </div>

    {{-- Invoice Header --}}
    <div class="invoice-header d-flex justify-content-between flex-wrap gap-3">
        <div>
            <h2 class="fw-bold text-success mb-0">{{ config('app.name') }}</h2>
            <p class="text-muted mb-0 small">Agricultural Solutions Platform</p>
        </div>
        <div class="text-end">
            <h4 class="fw-bold">INVOICE</h4>
            <p class="mb-0 small"><strong>#{{ $order->order_number }}</strong></p>
            <p class="mb-0 small text-muted">Date: {{ $order->created_at->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Bill To / Ship To --}}
    <div class="row mb-4">
        <div class="col-sm-6">
            <h6 class="text-uppercase text-muted mb-1 small">Bill To</h6>
            <p class="mb-0 fw-semibold">{{ $order->user->name }}</p>
            <p class="mb-0 small text-muted">{{ $order->user->email }}</p>
            @if($order->delivery_address)
                <p class="mb-0 small text-muted">{{ $order->delivery_address }}</p>
            @endif
        </div>
        <div class="col-sm-6 text-sm-end">
            <h6 class="text-uppercase text-muted mb-1 small">Sold By</h6>
            <p class="mb-0 fw-semibold">{{ $order->vendor->name ?? config('app.name') }}</p>
            <p class="mb-0 small text-muted">Order Status:
                <span class="badge-status bg-success-subtle text-success">{{ ucfirst($order->status) }}</span>
            </p>
            <p class="mb-0 small text-muted">Payment: {{ strtoupper($order->payment_method) }}</p>
        </div>
    </div>

    {{-- Order Items Table --}}
    <div class="table-responsive mb-4">
        <table class="table table-bordered invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="row justify-content-end">
        <div class="col-sm-5">
            <table class="table table-sm">
                <tr><td>Subtotal</td><td class="text-end">{{ number_format($order->subtotal, 2) }}</td></tr>
                @if($order->discount_amount > 0)
                <tr class="text-success">
                    <td>Discount
                        @if($order->coupon)<span class="badge bg-success-subtle text-success ms-1 small">{{ $order->coupon->code }}</span>@endif
                    </td>
                    <td class="text-end">-{{ number_format($order->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($order->delivery_fee > 0)
                <tr><td>Delivery Fee</td><td class="text-end">{{ number_format($order->delivery_fee, 2) }}</td></tr>
                @endif
                @if($order->tax_amount > 0)
                <tr><td>Tax</td><td class="text-end">{{ number_format($order->tax_amount, 2) }}</td></tr>
                @endif
                <tr class="fw-bold table-success">
                    <td>Grand Total</td>
                    <td class="text-end">{{ number_format($order->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <p class="text-center text-muted small mt-4">
        Thank you for shopping with {{ config('app.name') }}!
        <br>This is a computer-generated invoice and does not require a signature.
    </p>
</div>
@endsection
