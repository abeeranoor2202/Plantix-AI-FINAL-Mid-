@extends('expert.layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointment Management')

@section('content')
{{-- Stats row --}}
<div class="row g-4 mb-4">
    @foreach([
        ['label'=>'Total Appointments','key'=>'total','icon'=>'fa-calendar','color'=>'#3B82F6'],
        ['label'=>'Needs Review','key'=>'pending','icon'=>'fa-hourglass-half','color'=>'#F59E0B'],
        ['label'=>'Upcoming Soon','key'=>'upcoming','icon'=>'fa-clock','color'=>'#10B981'],
        ['label'=>'Completed','key'=>'completed','icon'=>'fa-check-circle','color'=>'#059669'],
        ['label'=>'Rejected','key'=>'rejected','icon'=>'fa-times-circle','color'=>'#EF4444'],
    ] as $s)
    <div class="col-xl col-md-4 col-sm-6">
        <div class="card-agri hover-lift p-4 text-center h-100 d-flex flex-column justify-content-center align-items-center bg-white">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px; background-color: {{ $s['color'] }}15; color: {{ $s['color'] }};">
                <i class="fas {{ $s['icon'] }} fs-4"></i>
            </div>
            <h3 class="fw-bold text-dark mb-1">{{ $stats[$s['key']] }}</h3>
            <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px; font-size: 11px;">{{ $s['label'] }}</span>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="card-agri border-0 mb-4 p-4 mt-2">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-uppercase fw-bold text-muted mb-2">Status Filter</label>
            <select name="status" class="form-agri py-2">
                <option value="">All Statuses</option>
                @foreach(['requested','pending','accepted','confirmed','rescheduled','completed','rejected','cancelled'] as $st)
                    <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>
                        {{ ucfirst($st) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small text-uppercase fw-bold text-muted mb-2">Date From</label>
            <input type="date" name="date_from" class="form-agri py-2" value="{{ $filters['date_from'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small text-uppercase fw-bold text-muted mb-2">Date To</label>
            <input type="date" name="date_to" class="form-agri py-2" value="{{ $filters['date_to'] ?? '' }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn-agri btn-agri-primary w-100 py-2">
                <i class="fas fa-filter me-2"></i>Filter
            </button>
            <a href="{{ route('expert.appointments.index') }}" class="btn-agri btn-agri-outline py-2 d-flex align-items-center justify-content-center" title="Reset Filters" style="width: 42px;">
                <i class="fas fa-undo"></i>
            </a>
        </div>
    </div>
</form>

{{-- Appointment List --}}
<div class="card-agri p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th class="py-3 px-4 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">ID</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">Farmer Details</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">Consultation Topic</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">Scheduled For</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">Status</th>
                    <th class="py-3 border-0 text-muted text-uppercase fw-bold" style="font-size: 12px;">Fee</th>
                    <th class="py-3 px-4 border-0 text-muted text-uppercase fw-bold text-end" style="font-size: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments->items() as $appt)
                <tr style="background: white; border-bottom: 1px solid var(--sidebar-border); transition: background 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                    <td class="px-4 py-3">
                        <a href="{{ route('expert.appointments.show', $appt) }}" class="fw-bold text-decoration-none" style="color: var(--agri-primary);">#{{ $appt->id }}</a>
                    </td>
                    <td class="py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px; font-size: 1.1rem; font-family: var(--font-heading);">
                                {{ strtoupper(substr($appt->user->name ?? 'F', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6">{{ $appt->user->name }}</div>
                                <div class="text-muted small"><i class="fas fa-envelope me-1 text-primary opacity-75"></i>{{ $appt->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="fw-medium text-dark bg-light rounded px-3 py-2 d-inline-block" style="font-size: 13px;">
                            <i class="fas fa-comment-dots text-muted me-2"></i>{{ Str::limit($appt->topic ?? 'General Consultation', 30) }}
                        </div>
                    </td>
                    <td class="py-3">
                        <div class="fw-bold text-dark mb-1"><i class="far fa-calendar-alt text-primary me-2"></i>{{ $appt->scheduled_at?->format('d M Y') }}</div>
                        <div class="text-muted small fw-medium"><i class="far fa-clock text-muted me-2"></i>{{ $appt->scheduled_at?->format('h:i A') }}</div>
                    </td>
                    <td class="py-3">
                        <span class="badge-agri bg-{{ $appt->status_badge }} bg-opacity-10 text-{{ $appt->status_badge }} border border-{{ $appt->status_badge }} border-opacity-25" style="padding: 0.35em 0.8em;">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </td>
                    <td class="py-3">
                        <span class="fw-bold text-dark">PKR {{ number_format($appt->fee) }}</span>
                    </td>
                    <td class="text-end px-4 py-3">
                        <a href="{{ route('expert.appointments.show', $appt) }}"
                           class="btn btn-sm btn-light border shadow-sm text-primary rounded-circle d-flex align-items-center justify-content-center ms-auto" style="width: 36px; height: 36px;" title="View Details">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 border-0">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 border border-dashed" style="width: 80px; height: 80px;">
                                <i class="far fa-calendar-times fs-2 text-muted opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-dark">No Consultations Found</h5>
                            <p class="text-muted small mb-0">Adjust your filters or wait for new farm consultation requests.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($appointments->hasPages())
    <div class="p-4 bg-light border-top text-center">
        {{ $appointments->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
