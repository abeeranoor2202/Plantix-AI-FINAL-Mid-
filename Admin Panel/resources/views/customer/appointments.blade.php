@extends('layouts.dashboard')

@section('title', 'My Appointments | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4">
            <!-- Main Content -->
            <div class="col-12">
                <div class="card-agri p-4" style="border: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-dark" style="font-size: 20px;">Appointments</h3>
                        <a href="{{ route('appointment.book') }}" class="btn-agri btn-agri-primary text-decoration-none" style="padding: 8px 16px; font-size: 14px;">
                            <i class="fas fa-plus me-1"></i> Book Appointment
                        </a>
                    </div>

                    <form method="GET" action="{{ route('appointments') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-md-3">
                            <label class="agri-label">Search</label>
                            <input type="text" name="search" class="form-agri" value="{{ request('search') }}" placeholder="Topic or expert name">
                        </div>
                        <div class="col-md-2">
                            <label class="agri-label">Status</label>
                            <select name="status" class="form-agri">
                                <option value="">All Statuses</option>
                                @foreach(['pending_payment','pending_expert_approval','confirmed','reschedule_requested','rescheduled','completed','rejected','cancelled'] as $appointmentStatus)
                                    <option value="{{ $appointmentStatus }}" @selected(request('status') === $appointmentStatus)>{{ ucfirst(str_replace('_', ' ', $appointmentStatus)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="agri-label">Type</label>
                            <select name="type" class="form-agri">
                                <option value="">All Types</option>
                                <option value="online" @selected(request('type') === 'online')>Online</option>
                                <option value="physical" @selected(request('type') === 'physical')>Physical</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="agri-label">Expert</label>
                            <select name="expert_id" class="form-agri">
                                <option value="">All Experts</option>
                                @foreach(($experts ?? collect()) as $expertFilter)
                                    <option value="{{ $expertFilter->id }}" @selected((string) request('expert_id') === (string) $expertFilter->id)>{{ $expertFilter->user->name ?? ('Expert #' . $expertFilter->id) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">From</label>
                            <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="agri-label">To</label>
                            <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1 d-flex gap-2 justify-content-end">
                            <button type="submit" class="btn-agri btn-agri-primary" style="padding: 8px 16px; font-size: 14px;">Apply</button>
                            <a href="{{ route('appointments') }}" class="btn-agri btn-agri-outline text-decoration-none" style="padding: 8px 16px; font-size: 14px;">Reset</a>
                        </div>
                    </form>

                    @if(session('success'))
                        <x-alert variant="success" class="mb-4">{{ session('success') }}</x-alert>
                    @endif

                    @if($appointments->isEmpty())
                        <div class="text-center py-5">
                            <div style="width: 80px; height: 80px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fas fa-calendar-times text-muted fs-1"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No appointments found</h5>
                            <p class="text-muted">No appointments match your current filters.</p>
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
                                        <x-badge :variant="$appt->type === 'physical' ? 'success' : 'info'">
                                            {{ strtoupper($appt->type_label) }}
                                        </x-badge>
                                    </td>
                                    <td class="border-bottom-0 py-3">
                                        <x-platform.status-badge domain="appointment" :status="$appt->status" />
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
                                                <button class="btn-agri btn-agri-danger" style="padding: 6px 12px; font-size: 13px; border: none;" onclick="return confirm('Are you sure you want to cancel this appointment?')"><i class="fas fa-times"></i></button>
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
