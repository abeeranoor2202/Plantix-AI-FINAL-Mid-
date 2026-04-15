@extends('layouts.frontend')

@section('title', 'My Appointments | Plantix-AI')

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
                        <a class="list-group-item border-0 text-muted py-3 px-4 d-flex align-items-center gap-3" href="{{ route('orders') }}">
                            <i class="fas fa-shopping-bag fs-5"></i> My Orders
                        </a>
                        <a class="list-group-item border-0 py-3 px-4 d-flex align-items-center gap-3 active" href="{{ route('appointments') }}" style="background: var(--agri-primary-light); color: var(--agri-primary); border-left: 4px solid var(--agri-primary) !important;">
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
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 20px;">Appointments</h3>
                        <a href="{{ route('appointment.book') }}" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 8px 16px; font-size: 14px;">
                            <i class="fas fa-plus me-1"></i> Book Appointment
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="border-radius: var(--agri-radius-sm);">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if($appointments->isEmpty())
                        <div class="text-center py-5">
                            <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-calendar-times text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No appointments found</h5>
                            <p class="text-muted">You haven't booked any expert consultations yet.</p>
                            <a href="{{ route('appointment.book') }}" class="btn-agri btn-agri-outline mt-2 text-decoration-none">Book your first session</a>
                        </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle" style="border-collapse: separate; border-spacing: 0 10px;">
                            <thead style="background: var(--agri-bg);">
                                <tr>
                                    <th class="border-0 py-3 rounded-start" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">ID</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Date / Time</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Expert</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Type</th>
                                    <th class="border-0 py-3" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Status</th>
                                    <th class="border-0 py-3 rounded-end" style="font-weight: 600; color: var(--agri-text-muted); font-size: 13px; text-transform: uppercase;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($appointments as $appt)
                                <tr style="background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                                    <td class="border-bottom-0 py-3 rounded-start">
                                        <a href="{{ route('appointment.details', $appt->id) }}" class="fw-bold text-decoration-none" style="color: var(--agri-primary);">#{{ $appt->id }}</a>
                                    </td>
                                    <td class="border-bottom-0 py-3 text-dark fw-medium">{{ $appt->scheduled_at ? $appt->scheduled_at->format('d M Y, h:i A') : '-' }}</td>
                                    <td class="border-bottom-0 py-3 text-muted">
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width: 32px; height: 32px; background: var(--agri-bg); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-user-md text-muted fs-6"></i>
                                            </div>
                                            {{ $appt->expert->user->name ?? 'Any Expert' }}
                                        </div>
                                    </td>
                                    <td class="border-bottom-0 py-3">
                                        <span class="badge rounded-pill fw-medium" style="background: {{ $appt->type === 'physical' ? 'rgba(16, 185, 129, 0.1); color: #10B981;' : 'rgba(37, 99, 235, 0.1); color: #2563EB;' }} padding: 6px 12px; font-size: 12px;">
                                            {{ strtoupper($appt->type_label) }}
                                        </span>
                                    </td>
                                    <td class="border-bottom-0 py-3">
                                        <span class="badge rounded-pill fw-medium" style="background: {{ $appt->status === 'completed' ? 'rgba(16, 185, 129, 0.1); color: #10B981;' : ($appt->status === 'cancelled' ? 'rgba(239, 68, 68, 0.1); color: #EF4444;' : 'rgba(245, 158, 11, 0.1); color: #F59E0B;') }} padding: 6px 12px; font-size: 12px;">
                                            {{ ucwords(str_replace('_', ' ', $appt->status)) }}
                                        </span>
                                    </td>
                                    <td class="border-bottom-0 py-3 rounded-end">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('appointment.details', $appt->id) }}" class="btn-agri text-decoration-none" style="padding: 6px 12px; font-size: 13px; background: var(--agri-bg); color: var(--agri-text-main);"><i class="fas fa-eye"></i></a>
                                            @if($appt->isOnline() && $appt->meeting_link)
                                            <a href="{{ $appt->meeting_link }}" target="_blank" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 6px 14px; font-size: 13px;">
                                                <i class="fas fa-video me-1"></i> Join Meeting
                                            </a>
                                            @endif
                                            @if($appt->status === 'pending_payment')
                                            <a href="{{ route('appointment.pay', $appt->id) }}" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 6px 14px; font-size: 13px;">
                                                <i class="fas fa-credit-card me-1"></i> Pay
                                            </a>
                                            @endif
                                            @if($appt->canBeCancelledByCustomer())
                                            <form method="POST" action="{{ route('appointment.cancel', $appt->id) }}">
                                                @csrf
                                                <button class="btn-agri text-danger" style="padding: 6px 12px; font-size: 13px; background: rgba(239, 68, 68, 0.1); border: none;" onclick="return confirm('Are you sure you want to cancel this appointment?')"><i class="fas fa-times"></i></button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $appointments->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
