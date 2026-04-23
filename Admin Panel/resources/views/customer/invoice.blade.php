<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .invoice-header { border-bottom: 3px solid #2e7d32; padding-bottom: 1rem; margin-bottom: 1.5rem; display: table; width: 100%; }
        .invoice-header > div { display: table-cell; vertical-align: top; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #2e7d32; }
        .text-muted { color: #6c757d; }
        .small { font-size: 0.875rem; }
        .mb-0 { margin-bottom: 0; }
        .fw-bold { font-weight: bold; }
        .table-bordered th, .table-bordered td { border: 1px solid #ddd; padding: 8px; }
        .invoice-table th { background: #1b5e20; color: #fff; }
        .badge-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: .75rem; font-weight: 600; text-transform: uppercase; background: #e8f5e9; color: #2e7d32; }
        .row-table { display: table; width: 100%; margin-bottom: 1.5rem; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; }
        .totals-table { width: 40%; float: right; }
        .totals-table th, .totals-table td { padding: 6px; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div style="width: 50%;">
            <h2 class="fw-bold text-success mb-0" style="margin-top:0;">{{ config('app.name') }}</h2>
            <p class="text-muted mb-0 small">Agricultural Solutions Platform</p>
        </div>
        <div class="text-end" style="width: 50%;">
            <h4 class="fw-bold" style="margin-top:0;">INVOICE</h4>
            <p class="mb-0 small"><strong>#{{ $order->order_number }}</strong></p>
            <p class="mb-0 small text-muted">Date: {{ $order->created_at->format('d M Y') }}</p>
        </div>
    </div>

    <div class="row-table">
        <div class="col-half">
            <h6 class="text-muted mb-0 small text-uppercase" style="margin-top:0;">Bill To</h6>
            <p class="mb-0 fw-bold">{{ $order->user->name }}</p>
            <p class="mb-0 small text-muted">{{ $order->user->email }}</p>
            @if($order->delivery_address)
                <p class="mb-0 small text-muted">{{ $order->delivery_address }}</p>
            @endif
        </div>
        <div class="col-half text-end">
            <h6 class="text-muted mb-0 small text-uppercase" style="margin-top:0;">Sold By</h6>
            <p class="mb-0 fw-bold">{{ $order->vendor->name ?? config('app.name') }}</p>
            <p class="mb-0 small text-muted">Order Status: <span class="badge-status">{{ ucfirst($order->status) }}</span></p>
            <p class="mb-0 small text-muted">Payment: {{ strtoupper($order->payment_method) }}</p>
        </div>
    </div>

    <table class="table-bordered invoice-table">
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 45%; text-align: left;">Product</th>
                <th class="text-center" style="width: 10%">Qty</th>
                <th class="text-end" style="width: 20%">Unit Price</th>
                <th class="text-end" style="width: 20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <table class="totals-table">
            <tr><td>Subtotal</td><td class="text-end">{{ number_format($order->subtotal, 2) }}</td></tr>
            @if($order->discount_amount > 0)
            <tr class="text-success">
                <td>Discount
                    @if($order->coupon)<small>({{ $order->coupon->code }})</small>@endif
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
            <tr style="border-top: 2px solid #ddd;">
                <td class="fw-bold">Grand Total</td>
                <td class="text-end text-success"><strong style="font-size: 1.1em;">PKR {{ number_format($order->total, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <p class="text-center text-muted small" style="margin-top: 60px;">
        Thank you for shopping with {{ config('app.name') }}!
        <br>This is a computer-generated invoice and does not require a signature.
    </p>
</body>
</html>
