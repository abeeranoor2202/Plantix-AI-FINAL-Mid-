@extends('vendor.layouts.app')
@section('title', 'Return #' . $return->id)
@section('page-title', 'Return Request Detail')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex align-items-center mb-4">
    <a href="{{ route('vendor.returns.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle me-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;" title="Back to Returns">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Return Request #{{ $return->id }}</h4>
        <span class="text-muted small fw-medium mt-1 d-block">Manage and provide feedback for this customer return</span>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm hover-card mb-4" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-file-earmark-text me-2 text-success fs-5"></i>Return Details</h6>
            </div>
            <div class="card-body p-4">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Return Ref</dt>
                    <dd class="col-sm-8 mb-3"><strong class="font-monospace text-dark bg-light px-2 py-1 rounded shadow-sm border border-secondary border-opacity-25">#{{ $return->id }}</strong></dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Order #</dt>
                    <dd class="col-sm-8 mb-3"><a href="{{ route('vendor.orders.show', $return->order_id) }}" class="text-decoration-none fw-bold text-primary"><i class="bi bi-receipt me-1"></i>{{ $return->order->order_number ?? '—' }}</a></dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Customer</dt>
                    <dd class="col-sm-8 mb-3 fw-medium text-dark"><i class="bi bi-person-circle text-muted me-1"></i>{{ $return->user->name ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Reason</dt>
                    <dd class="col-sm-8 mb-3 fw-medium text-dark">{{ $return->reason->title ?? $return->reason->reason ?? $return->notes ?? '—' }}</dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Description</dt>
                    <dd class="col-sm-8 mb-3 text-muted bg-light p-3 rounded-3">{{ $return->notes ?? 'No extra description provided.' }}</dd>

                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mb-3">Status</dt>
                    <dd class="col-sm-8 mb-3">
                        @php 
                            $badgeMap = [
                                'pending'  => 'warning text-dark',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'refund_processing' => 'primary',
                                'completed' => 'info',
                            ];
                            $iconMap = [
                                'pending'  => 'bi-hourglass-split',
                                'approved' => 'bi-check-circle-fill',
                                'rejected' => 'bi-x-circle-fill',
                                'refund_processing' => 'bi-arrow-repeat',
                                'completed' => 'bi-cash-coin',
                            ];
                            $badge = $badgeMap[$return->status] ?? 'secondary';
                            $icon = $iconMap[$return->status] ?? 'bi-info-circle';
                        @endphp
                        <span class="badge bg-{{ $badge }} bg-opacity-10 text-{{ str_replace(' text-dark', '', $badge) }} border border-{{ str_replace(' text-dark', '', $badge) }} border-opacity-25 rounded-pill px-3 py-1 text-capitalize shadow-sm fs-6">
                            <i class="bi {{ $icon }} me-1"></i>{{ ucfirst($return->status) }}
                        </span>
                    </dd>

                    @if($return->items->count())
                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mt-4 mb-2">Requested Items</dt>
                    <dd class="col-sm-8 mt-4 mb-2">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="small text-muted text-uppercase">Product</th>
                                        <th class="small text-muted text-uppercase text-end">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->items as $item)
                                        <tr>
                                            <td class="small text-dark fw-medium">{{ $item->product->name ?? 'Unknown Product' }}</td>
                                            <td class="small text-end text-dark fw-bold">{{ $item->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </dd>
                    @endif

                    @if($return->admin_notes)
                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mt-4 mb-2">Admin Notes</dt>
                    <dd class="col-sm-8 mt-4 mb-2">
                        <div class="alert alert-primary bg-primary bg-opacity-10 border-0 text-primary small mb-0 rounded-3">
                            <i class="bi bi-shield-lock-fill me-2"></i>{{ $return->admin_notes }}
                        </div>
                    </dd>
                    @endif

                    @if($return->vendor_notes)
                    <dt class="col-sm-4 text-muted small text-uppercase fw-bold mt-4 mb-2">Your Notes</dt>
                    <dd class="col-sm-8 mt-4 mb-2">
                        <div class="alert alert-secondary bg-secondary bg-opacity-10 border-0 text-secondary small mb-0 rounded-3">
                            <i class="bi bi-chat-left-text-fill me-2"></i>{{ $return->vendor_notes }}
                        </div>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Add Vendor Note (only when pending) --}}
        @if($return->status === 'pending')
        <div class="card border-0 shadow-sm hover-card" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-chat-dots-fill me-2 text-warning fs-5"></i>Add Note for Admin</h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-warning bg-warning bg-opacity-10 border-0 text-dark small mb-4 rounded-3 d-flex align-items-center">
                    <i class="bi bi-info-circle-fill text-warning fs-5 me-3"></i>
                    <div>Provide context about this return. The Admin team will make the final decision.</div>
                </div>
                <form method="POST" action="{{ route('vendor.returns.note', $return->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase fw-bold small mb-2">Your Context / Evidence</label>
                        <textarea name="notes" class="form-control form-control-lg fs-6 bg-light border-0 rounded-3 @error('notes') is-invalid @enderror"
                                  rows="4" placeholder="e.g. Customer received correct item, please review photos..."
                                  required>{{ old('notes', $return->vendor_notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-warning rounded-pill fw-bold px-4 py-2 shadow-sm text-dark">
                        <i class="bi bi-send-fill me-2"></i>Submit Note
                    </button>
                </form>
            </div>
        </div>

        {{-- Approve / Reject Actions --}}
        <div class="card border-0 shadow-sm mt-4" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-check me-2 text-primary fs-5"></i>Take Action</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">Approve to accept the return and restore stock, or reject with a reason for the customer.</p>
                <div class="row g-3">
                    {{-- Approve --}}
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('vendor.returns.approve', $return->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-2">Approval Note (optional)</label>
                                <textarea name="admin_notes" rows="3" class="form-control bg-light border-0 rounded-3 fs-6"
                                          placeholder="e.g. Return approved, please ship back the item..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success rounded-pill fw-bold px-4 py-2 w-100 shadow-sm"
                                    onclick="return confirm('Approve this return request? Stock will be restored.')">
                                <i class="bi bi-check-circle-fill me-2"></i>Approve Return
                            </button>
                        </form>
                    </div>
                    {{-- Reject --}}
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('vendor.returns.reject', $return->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-2">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="admin_notes" rows="3" required class="form-control bg-light border-0 rounded-3 fs-6 @error('admin_notes') is-invalid @enderror"
                                          placeholder="e.g. Return window expired, item shows signs of use..."></textarea>
                                @error('admin_notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-danger rounded-pill fw-bold px-4 py-2 w-100 shadow-sm"
                                    onclick="return confirm('Reject this return request?')">
                                <i class="bi bi-x-circle-fill me-2"></i>Reject Return
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Order Items --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm hover-card" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam-fill me-2 text-info fs-5"></i>Items in this Order</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush rounded-bottom-4">
                        @forelse($return->order->items ?? [] as $item)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-4 py-3 hover-bg-light transition-all">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center me-3 border shadow-sm" style="width: 48px; height: 48px;">
                                <i class="bi bi-image text-muted fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark fs-6">{{ Str::limit($item->product->name ?? 'Unknown Product', 25) }}</h6>
                                <div class="text-muted small fw-medium">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 me-2">Qty: {{ $item->quantity }}</span>
                                    {{ config('plantix.currency_symbol') }}{{ number_format($item->unit_price, 2) }} each
                                </div>
                            </div>
                        </div>
                                <span class="fw-bold text-success">{{ config('plantix.currency_symbol') }}{{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-muted text-center py-5">
                        <i class="bi bi-dash-circle fs-3 d-block mb-2 opacity-50"></i>
                        No items found in this order.
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
