@extends('layouts.frontend')

@section('title', 'Plantix-AI')


@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<!-- Breadcrumb -->
  <div class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
    style="background-image: url({{ asset('assets/img/banner7.jpg') }});">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 offset-lg-2">
          <h1>Order Details</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li><a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a></li>
              <li><a href="{{ route('orders') }}">Orders</a></li>
              <li class="active">Order</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Details -->
  <div id="order-details-page" class="default-padding">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="panel-card p-4">

            @if(session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <h3 class="mb-0">Order #{{ $order->id }}</h3>
              <div class="d-flex gap-2 flex-wrap">
                @if(in_array($order->status, ['pending','confirmed']))
                <form method="POST" action="{{ route('order.cancel', $order->id) }}">
                  @csrf
                  <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Cancel this order?')">Cancel</button>
                </form>
                @endif
                @if($order->status === 'delivered' && !$order->returnRequest)
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">Request Return</button>
                @endif
                <a href="{{ route('orders') }}" class="btn btn-border btn-sm">Back to Orders</a>
                <a href="{{ route('shop') }}" class="btn btn-theme btn-sm">Continue Shopping</a>
              </div>
            </div>
            <hr>
            <div class="row g-4">
              <div class="col-md-6">
                <h5>Summary</h5>
                <ul class="list-unstyled mb-0">
                  <li><strong>Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</li>
                  <li><strong>Status:</strong> <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">{{ ucfirst($order->status) }}</span></li>
                  <li><strong>Payment:</strong> {{ strtoupper($order->payment_method ?? 'N/A') }}</li>
                  <li><strong>Total:</strong> PKR {{ number_format($order->total ?? 0, 2) }}</li>
                </ul>
              </div>
              <div class="col-md-6">
                <h5>Ship To</h5>
                <address class="mb-0">{{ $order->delivery_address ?? '-' }}</address>
              </div>
              <div class="col-12">
                <h5>Items</h5>
                <div class="table-responsive">
                  <table class="table table-striped align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Line Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($order->admin->items ?? [] as $item)
                      <tr>
                        <td>{{ $item->product->name ?? 'Product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>PKR {{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td>PKR {{ number_format(($item->unit_price ?? 0) * $item->quantity, 2) }}</td>
                      </tr>
                      @empty
                      <tr><td colspan="4" class="text-muted text-center">No items found.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Return Modal --}}
  @if($order->status === 'delivered' && !isset($order->returnRequest))
  <div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('order.return', $order->id) }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Request Return</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Reason *</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Describe the reason for return" required></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-theme">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  @endif

  <!-- Footer -->
@endsection

