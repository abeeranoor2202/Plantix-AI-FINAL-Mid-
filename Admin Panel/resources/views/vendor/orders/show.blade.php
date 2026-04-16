@extends('vendor.layouts.app')
@section('title', 'Order #' . $order->id)
@section('page-title', 'Order Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('vendor.orders.index') }}" class="btn btn-light border rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
            <i class="fas fa-arrow-left text-muted"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                Order #{{ $order->id }}
                <span class="badge-agri border badge-{{ match($order->status) {
                    'pending'=>'warning','accepted'=>'info','preparing'=>'primary',
                    'ready'=>'success','delivered'=>'success','cancelled'=>'danger',
                    default=>'secondary'} }}-agri border-{{ match($order->status) {
                    'pending'=>'warning','accepted'=>'info','preparing'=>'primary',
                    'ready'=>'success','delivered'=>'success','cancelled'=>'danger',
                    default=>'secondary'} }} border-opacity-25 ms-2 shadow-sm" style="font-size: 14px; padding: 0.3em 1em;">
                    {{ ucfirst($order->status) }}
                </span>
            </h4>
            <span class="text-muted small fw-medium mt-1 d-block"><i class="far fa-clock me-1"></i>Placed on {{ $order->created_at->format('M d, Y at h:i A') }}</span>
        </div>
    </div>
    <button class="btn-agri btn-agri-outline shadow-sm px-4" onclick="window.print()">
        <i class="fas fa-print me-2"></i> Print Invoice
    </button>
</div>

<div class="row g-4">
    {{-- Order Items --}}
    <div class="col-lg-8">
        <div class="card-agri p-0 overflow-hidden border-0 mb-4 h-100">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-shopping-bag text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Items Purchased</h5>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="background: white;">
                        <tr>
                            <th class="py-3 px-4 border-bottom text-muted text-uppercase fw-bold" style="font-size: 12px;">Product Detail</th>
                            <th class="text-center py-3 border-bottom text-muted text-uppercase fw-bold" style="font-size: 12px;">Quantity</th>
                            <th class="text-end py-3 border-bottom text-muted text-uppercase fw-bold" style="font-size: 12px;">Unit Price</th>
                            <th class="text-end py-3 px-4 border-bottom text-muted text-uppercase fw-bold" style="font-size: 12px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr style="border-bottom: 1px solid var(--sidebar-border);">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px; min-width: 48px;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->product->name ?? $item->name }}</div>
                                        @if($item->variant)<div class="small text-muted mt-1"><i class="fas fa-tag me-1"></i>{{ $item->variant }}</div>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge-agri bg-light text-dark border px-3 py-1 fs-6">{{ $item->quantity }}</span>
                            </td>
                            <td class="text-end py-3 fw-medium text-muted">{{ config('plantix.currency_symbol') }}{{ number_format($item->price, 2) }}</td>
                            <td class="text-end px-4 py-3 fw-bold text-dark">{{ config('plantix.currency_symbol') }}{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: var(--agri-bg);">
                        <tr>
                            <td colspan="3" class="text-end fw-bold py-3 text-muted border-0">Subtotal</td>
                            <td class="text-end px-4 py-3 fw-bold text-dark border-0">{{ config('plantix.currency_symbol') }}{{ number_format($order->sub_total ?? $order->total, 2) }}</td>
                        </tr>
                        @if($order->coupon_discount)
                        <tr>
                            <td colspan="3" class="text-end py-2 fw-bold text-success border-0"><i class="fas fa-ticket-alt me-2"></i>Coupon Discount</td>
                            <td class="text-end px-4 py-2 fw-bold text-success border-0">-{{ config('plantix.currency_symbol') }}{{ number_format($order->coupon_discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end py-4 fw-bold text-dark fs-5 border-0">Grand Total</td>
                            <td class="text-end px-4 py-4 fw-bold text-primary fs-4 border-0">{{ config('plantix.currency_symbol') }}{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar: Customer + Update Status --}}
    <div class="col-lg-4 d-flex flex-column gap-4">
        {{-- Customer Info --}}
        <div class="card-agri p-0 border-0">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-address-card text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Customer Info</h5>
            </div>
            
            <div class="p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-dashed">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white shadow-sm me-3"
                         style="width: 56px; height: 56px; font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading);">
                        {{ strtoupper(substr($order->user->name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold text-dark fs-5">{{ $order->user->name ?? 'N/A' }}</h6>
                        <span class="small text-muted d-flex align-items-center"><i class="fas fa-envelope text-primary me-2"></i>{{ $order->user->email ?? 'No email provided' }}</span>
                    </div>
                </div>

                @if($order->user->phone)
                <div class="d-flex align-items-start mb-4 text-muted">
                    <div class="bg-light rounded p-2 me-3 text-primary d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <span class="d-block small text-uppercase fw-bold text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Phone Number</span>
                        <span class="fw-bold text-dark fs-6">{{ $order->user->phone }}</span>
                    </div>
                </div>
                @endif
                
                @if($order->delivery_address)
                <div class="d-flex align-items-start text-muted">
                    <div class="bg-light rounded p-2 me-3 text-danger d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <span class="d-block small text-uppercase fw-bold text-muted mb-1" style="font-size: 11px; letter-spacing: 0.5px;">Delivery Address</span>
                        <p class="mb-0 fw-medium text-dark" style="line-height: 1.6; font-size: 14px;">{{ $order->delivery_address }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Update Status --}}
        @php $nextStatuses = \App\Models\Order::allowedTransitions()[$order->status] ?? []; @endphp
        @if(count($nextStatuses) > 0)
        <div class="card-agri p-0 border-0">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-sync-alt text-warning fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Update Status</h5>
            </div>
            
            <div class="p-4">
                <form method="POST" action="{{ route('vendor.orders.status', $order->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small mb-2">Order Progress</label>
                        <select name="status" class="form-agri py-2 fw-medium shadow-none">
                            @foreach($nextStatuses as $s)
                                <option value="{{ $s }}">⟷ {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-dark fw-bold small mb-2">Additional Note (Optional)</label>
                        <textarea name="notes" rows="3" class="form-agri py-2 shadow-none"
                                  placeholder="Message to customer regarding this update..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-agri w-100 shadow-sm" style="background-color: var(--agri-secondary); color: var(--agri-dark);">
                        <i class="fas fa-check-circle me-2"></i> Apply Status Update
                    </button>
                </form>
            </div>
        </div>
        @endif

        <div class="card-agri p-0 border-0">
            <div class="p-4 bg-light border-bottom d-flex align-items-center gap-2">
                <i class="fas fa-stream text-info fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Status Timeline</h5>
            </div>

            <div class="p-4">
                @forelse($order->statusHistory as $history)
                    <div class="pb-3 mb-3 border-bottom border-dashed">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-bold text-dark">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</div>
                                @if($history->notes)
                                    <div class="small text-muted mt-1">{{ $history->notes }}</div>
                                @endif
                            </div>
                            <div class="small text-muted text-end">
                                <div>{{ $history->created_at?->format('M d, Y h:i A') }}</div>
                                @if($history->changedBy)
                                    <div>by {{ $history->changedBy->name }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No status history available yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
