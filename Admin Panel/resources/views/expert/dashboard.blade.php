@extends('expert.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <span style="color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Expert Panel</span>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Dashboard</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Dashboard</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Expert activity and consultation summary.</p>
    </div>
    <x-button :href="route('expert.appointments.index')" variant="primary" icon="fas fa-calendar-check">View Appointments</x-button>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card-agri" style="cursor: pointer;" onclick="location.href='{{ route('expert.appointments.index') }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div style="background: var(--agri-primary-light); padding: 10px; border-radius: 12px;">
                    <i class="mdi mdi-calendar-check" style="color: var(--agri-primary); font-size: 24px;"></i>
                </div>
            </div>
            <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Total Appointments</h5>
            <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['total'] ?? 0 }}</h2>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-agri" style="cursor: pointer;" onclick="location.href='{{ route('expert.appointments.index', ['status' => 'pending_expert_approval']) }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div style="background: #FFFBEB; padding: 10px; border-radius: 12px;">
                    <i class="mdi mdi-clock-outline" style="color: var(--agri-secondary); font-size: 24px;"></i>
                </div>
            </div>
            <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Pending</h5>
            <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['pending'] ?? 0 }}</h2>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-agri" style="cursor: pointer;" onclick="location.href='{{ route('expert.appointments.index', ['status' => 'confirmed']) }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div style="background: #F0FDF4; padding: 10px; border-radius: 12px;">
                    <i class="mdi mdi-calendar-clock" style="color: var(--agri-primary); font-size: 24px;"></i>
                </div>
            </div>
            <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Upcoming</h5>
            <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['upcoming'] ?? 0 }}</h2>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-agri" style="cursor: pointer;" onclick="location.href='{{ route('expert.appointments.index', ['status' => 'completed']) }}'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <div style="background: #F0FDF4; padding: 10px; border-radius: 12px;">
                    <i class="mdi mdi-check-circle" style="color: var(--agri-primary-hover); font-size: 24px;"></i>
                </div>
            </div>
            <h5 style="color: var(--agri-text-muted); font-size: 14px; margin-bottom: 4px;">Completed</h5>
            <h2 style="font-size: 28px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">{{ $stats['completed'] ?? 0 }}</h2>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-agri" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Recent Pending Requests</h3>
                <a href="{{ route('expert.appointments.index') }}" style="color: var(--agri-primary); font-size: 14px; font-weight: 600; text-decoration: none;">View All</a>
            </div>

            <x-table>
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 12px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">FARMER</th>
                        <th style="padding: 12px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TOPIC</th>
                        <th style="padding: 12px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">SCHEDULED</th>
                        <th style="padding: 12px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;" class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requested->items() as $appt)
                        <tr>
                            <td style="padding: 14px 24px;">
                                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $appt->user->name }}</div>
                                <small class="text-muted">{{ $appt->user->email }}</small>
                            </td>
                            <td style="padding: 14px 24px;">{{ Str::limit($appt->topic ?? 'General consultation', 40) }}</td>
                            <td style="padding: 14px 24px;">{{ $appt->scheduled_at?->format('d M Y, h:i A') }}</td>
                            <td style="padding: 14px 24px;" class="text-end">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('expert.appointments.show', $appt) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                    <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #9ca3af; border-radius: 999px; border: none;" title="Edit unavailable" disabled><i class="fas fa-pen"></i></button>
                                    <button type="button" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #fca5a5; border-radius: 999px; border: none;" title="Delete unavailable" disabled><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5" style="color: var(--agri-text-muted);">
                                <i class="mdi mdi-inbox" style="font-size: 28px; display:block; margin-bottom: 8px; opacity: .5;"></i>
                                No pending requests.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-table>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-agri" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Profile</h3>
                <a href="{{ route('expert.profile.edit') }}" style="color: var(--agri-primary); font-size: 14px; font-weight: 600; text-decoration: none;">Edit</a>
            </div>
            <div style="padding: 24px;">
                <div style="font-weight: 700; color: var(--agri-text-heading);">{{ $expert->user->name }}</div>
                <div class="text-muted" style="margin-top: 4px;">{{ $expert->specialty }}</div>
                <div style="margin-top: 16px; display: grid; gap: 8px;">
                    <div><strong>City:</strong> {{ $expert->profile?->city ?? 'N/A' }}</div>
                    <div><strong>Experience:</strong> {{ $expert->profile?->experience_years ?? 0 }} years</div>
                    <div><strong>Rate:</strong> PKR {{ number_format($expert->hourly_rate) }}</div>
                    <div><strong>Status:</strong> 
                        <x-badge :variant="$expert->is_available ? 'success' : 'danger'">{{ $expert->is_available ? 'Available' : 'Unavailable' }}</x-badge>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
