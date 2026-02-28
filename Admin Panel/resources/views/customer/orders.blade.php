@extends('layouts.frontend')

@section('title', 'My Orders | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Sidebar Menu -->
            <div class="col-lg-3 mb-4">
                <div class="card-agri p-0 overflow-hidden" style="border: none;">
                    <div class="bg-white p-4 text-center border-bottom">
                        <div style="width: 80px; height: 80px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 32px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ auth('web')->user()->name ?? 'Customer' }}</h5>
                        <p class="text-muted small mb-0">{{ auth('web')->user()->email ?? '' }}</p>
                    </div>
                    <div class="list-group border-0" style="border-radius: 0;">
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('account.profile') }}">
                            <i class="fas fa-user-circle fs-5"></i> Profile Settings
                        </a>
                        <a class="list-group-item border-0 py-3 px-4 d-flex align-items-center gap-3 active" href="{{ route('orders') }}" style="background: var(--agri-primary-light); color: var(--agri-primary); border-left: 4px solid var(--agri-primary) !important;">
                            <i class="fas fa-shopping-bag fs-5"></i> My Orders
                        </a>
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('appointments') }}">
                            <i class="fas fa-calendar-check fs-5"></i> Appointments
                        </a>
                        <a class="list-group-item border-0 text-danger py-3 px-4 d-flex align-items-center gap-3 mt-3 border-top" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fs-5"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card-agri p-4" style="border: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 20px;">Order History</h3>
                        <a href="{{ route('shop') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 8px 16px; font-size: 14px;">Continue Shopping</a>
                    </div>

                    <div id="ordersListTable" class="table-responsive">
                        <table class="table align-middle" style="border-collapse: separate; border-spacing: 0 10px;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th class="border-0 py-3 rounded-start" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Order #</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Date</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Items</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Total</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Status</th>
                                    <th class="border-0 py-3 rounded-end" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Order rows will be populated via JS or backend, currently stubbed -->
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Loading orders...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
