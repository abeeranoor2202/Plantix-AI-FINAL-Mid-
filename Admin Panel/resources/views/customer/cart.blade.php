@extends('layouts.frontend')

@section('title', 'Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<!-- End Header -->

    <!-- Start Breadcrumb 
    ============================================= -->
    <div
      class="breadcrumb-area text-center shadow dark-hard bg-cover text-light bg-breadcrumb-default"
      style="background-image: url({{ asset('assets/img/banner7.jpg') }})"
    >
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <h1>Shopping Cart</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li>
                  <a href="{{ route('home') }}"><i class="fas fa-home"></i> Home</a>
                </li>
                <li><a href="{{ route('shop') }}">Shop</a></li>
                <li class="active">Cart</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Start Cart -->
    <div class="cart-area default-padding">
      <div class="container">

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
          <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @php
          $items    = $cart->items ?? collect();
          $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
          $couponDiscount = session('coupon_discount', 0);
          $shipping = 500;
          $tax      = round(($subtotal - $couponDiscount) * 0.05);
          $total    = max(0, $subtotal - $couponDiscount) + $shipping + $tax;
        @endphp

        <div class="row">
          <div class="col-lg-8">
            <div class="cart-table-area">
              <h3>Your Cart</h3>
              @if($items->isEmpty())
                <p class="text-muted">Your cart is empty. <a href="{{ route('shop') }}">Browse the shop</a>.</p>
              @else
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Price</th>
                      <th>Quantity</th>
                      <th>Subtotal</th>
                      <th>Remove</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($items as $item)
                    @php $product = $item->product; @endphp
                    <tr>
                      <td class="d-flex align-items-center gap-2">
                        @if($product->primaryImage)
                          <img src="{{ Storage::url($product->primaryImage->path) }}" alt="{{ $product->name }}" width="55" height="55" style="object-fit:cover;border-radius:4px">
                        @endif
                        <a href="{{ route('shop.single', $product->id) }}">{{ $product->name }}</a>
                      </td>
                      <td>PKR {{ number_format($item->unit_price, 2) }}</td>
                      <td style="width:150px">
                        <form method="POST" action="{{ route('cart.update', $item->id) }}" class="d-flex gap-1 align-items-center">
                          @csrf @method('PATCH')
                          <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="99" class="form-control form-control-sm" style="width:70px">
                          <button class="btn btn-sm btn-outline-secondary" type="submit">Update</button>
                        </form>
                      </td>
                      <td>PKR {{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                      <td>
                        <form method="POST" action="{{ route('cart.remove', $item->id) }}">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger" type="submit"><i class="fas fa-times"></i></button>
                        </form>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              @endif
              <div class="cart-actions d-flex gap-2 mt-3">
                <a href="{{ route('shop') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
                @if(!$items->isEmpty())
                <form method="POST" action="{{ route('cart.clear') }}">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger" type="submit"><i class="fas fa-trash"></i> Clear Cart</button>
                </form>
                @endif
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="cart-summary">
              <h4>Cart Summary</h4>
              <ul class="summary-list">
                <li><span>Subtotal:</span><span>PKR {{ number_format($subtotal, 2) }}</span></li>
                @if($couponDiscount > 0)
                <li><span>Discount ({{ session('coupon_code') }}):</span><span>- PKR {{ number_format($couponDiscount, 2) }}</span></li>
                @endif
                <li><span>Shipping:</span><span>PKR {{ number_format($shipping, 2) }}</span></li>
                <li><span>Tax (5%):</span><span>PKR {{ number_format($tax, 2) }}</span></li>
                <li class="total-row"><strong>Total:</strong><strong>PKR {{ number_format($total, 2) }}</strong></li>
              </ul>

              {{-- Coupon form --}}
              @if(session('coupon_code'))
                <div class="mb-3">
                  <span class="badge bg-success">{{ session('coupon_code') }} applied</span>
                  <form method="POST" action="{{ route('cart.coupon.remove') }}" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger ms-2">Remove</button>
                  </form>
                </div>
              @else
                <form method="POST" action="{{ route('cart.coupon.apply') }}" class="promo-code d-flex gap-1 mb-2">
                  @csrf
                  <input type="text" name="code" class="form-control" placeholder="Promo Code">
                  <button class="btn btn-sm btn-outline-secondary" type="submit">Apply</button>
                </form>
                @error('coupon')<small class="text-danger">{{ $message }}</small>@enderror
              @endif

              <a href="{{ route('checkout') }}" class="btn btn-theme btn-md w-100 mt-3">
                <i class="fas fa-lock"></i> Proceed to Checkout
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- End Cart -->
@endsection

