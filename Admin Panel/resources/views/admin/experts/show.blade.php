@extends('layouts.app')

@section('title', 'Expert: ' . ($expert->user->name ?? 'Unknown'))

@section('content')
@php
    $profile    = $expert->profile;
    $approval   = $profile->approval_status ?? 'pending';
    $approvalMap = [
        'approved'  => ['#059669', '#D1FAE5', 'Approved'],
        'pending'   => ['#D97706', '#FEF3C7', 'Pending Review'],
        'rejected'  => ['#DC2626', '#FEE2E2', 'Rejected'],
        'suspended' => ['#6B7280', '#F3F4F6', 'Suspended'],
    ];
    $ac = $approvalMap[$approval] ?? ['#9CA3AF', '#F9FAFB', ucfirst($approval)];
    $currency = config('plantix.currency_symbol', 'PKR');
@endphp

<div class="container-fluid" style="padding-top: 24px; padding-bottom: 48px;">

    {{-- Breadcrumb / Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-user-tie"></i> Experts
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">{{ $expert->user->name ?? 'Unknown' }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">{{ $expert->user->name ?? 'Expert #' . $expert->id }}</h1>
                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $ac[0] }}; background: {{ $ac[1] }}; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; border: 1px solid {{ $ac[0] }}40; text-transform: uppercase; letter-spacing: 0.5px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $ac[0] }};"></span>
                    {{ $ac[2] }}
                </div>
            </div>
        </div>
        <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <ul style="margin: 0; padding-left: 20px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- ═══════ LEFT COLUMN ═══════ --}}
        <div class="col-lg-8">

            {{-- Expert Profile Card --}}
            <div class="card-agri mb-4" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Profile Information</h5>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-user" style="margin-right: 6px; color: var(--agri-primary);"></i>Account</p>
                            <p style="margin: 0; font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $expert->user->name ?? '—' }}</p>
                            <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $expert->user->email ?? '' }}</p>
                            @if($expert->user->phone ?? null)
                                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);"><i class="fas fa-phone" style="font-size: 11px; margin-right: 4px;"></i>{{ $expert->user->phone }}</p>
                            @endif
                            <p style="margin: 6px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">Joined {{ $expert->created_at?->format('M j, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-leaf" style="margin-right: 6px; color: var(--agri-primary);"></i>Expertise</p>
                            <p style="margin: 0; font-size: 15px; font-weight: 700; color: var(--agri-text-heading);">{{ $expert->specialty ?? $profile?->specialization ?? '—' }}</p>
                            @if($profile?->experience_years)
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $profile->experience_years }} years experience</p>
                            @endif
                            @if($profile?->account_type)
                                <p style="margin: 4px 0 0 0; font-size: 12px; background: var(--agri-primary-light); color: var(--agri-primary); display: inline-block; padding: 2px 10px; border-radius: 6px; font-weight: 600; text-transform: capitalize;">{{ $profile->account_type }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-map-marker-alt" style="margin-right: 6px; color: var(--agri-primary);"></i>Location</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">
                                {{ $profile?->city ?? '—' }}@if($profile?->country), {{ $profile->country }}@endif
                            </p>
                            @if($profile?->contact_phone)
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);"><i class="fas fa-phone" style="font-size: 11px; margin-right: 4px;"></i>{{ $profile->contact_phone }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-money-bill-wave" style="margin-right: 6px; color: var(--agri-success);"></i>Rate</p>
                            <p style="margin: 0; font-size: 24px; font-weight: 900; color: var(--agri-primary-dark);">{{ $currency }} {{ number_format($expert->hourly_rate ?? 0, 2) }}</p>
                            <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">per consultation session</p>
                        </div>
                    </div>
                    @if($expert->bio)
                    <div class="col-12">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-align-left" style="margin-right: 6px; color: var(--agri-primary);"></i>Bio</p>
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main); line-height: 1.7;">{{ $expert->bio }}</p>
                        </div>
                    </div>
                    @endif
                    @if($profile?->certifications)
                    <div class="col-12">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-certificate" style="margin-right: 6px; color: #D97706;"></i>Certifications</p>
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main); line-height: 1.7;">{{ $profile->certifications }}</p>
                        </div>
                    </div>
                    @endif
                    @if($profile?->website || $profile?->linkedin)
                    <div class="col-12">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px; display: flex; gap: 16px; flex-wrap: wrap;">
                            @if($profile?->website)
                                <a href="{{ $profile->website }}" target="_blank" style="font-size: 13px; color: #2563EB; display: flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <i class="fas fa-globe"></i> {{ $profile->website }}
                                </a>
                            @endif
                            @if($profile?->linkedin)
                                <a href="{{ $profile->linkedin }}" target="_blank" style="font-size: 13px; color: #0A66C2; display: flex; align-items: center; gap: 6px; text-decoration: none;">
                                    <i class="fab fa-linkedin"></i> LinkedIn Profile
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Specialization Tags --}}
            @if($expert->specializations && $expert->specializations->count())
            <div class="card-agri mb-4" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-tags"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Specializations</h6>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($expert->specializations as $spec)
                        <span style="background: var(--agri-primary-light); color: var(--agri-primary); padding: 6px 14px; border-radius: 100px; font-size: 13px; font-weight: 600;">{{ $spec->name ?? $spec->value ?? $spec }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Appointments --}}
            @if($expert->appointments && $expert->appointments->count())
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-calendar-check"></i></div>
                    <div>
                        <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Recent Appointments</h6>
                        <p style="margin: 0; font-size: 12px; color: var(--agri-text-muted);">Last 10 bookings · {{ $expert->appointments_count }} total</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Scheduled</th>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Status</th>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: end;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expert->appointments as $appt)
                            @php
                                $bc = [
                                    'confirmed'               => ['#059669', '#D1FAE5'],
                                    'completed'               => ['#0D9488', '#CCFBF1'],
                                    'cancelled'               => ['#9CA3AF', '#F9FAFB'],
                                    'pending_expert_approval' => ['#7C3AED', '#EDE9FE'],
                                ];
                                $sColor = $bc[$appt->status] ?? ['#D97706', '#FEF3C7'];
                            @endphp
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 14px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-heading);">{{ $appt->user->name ?? '—' }}</td>
                                <td style="padding: 14px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $appt->scheduled_at?->format('M j, Y g:i A') ?? 'TBD' }}</td>
                                <td style="padding: 14px 24px; text-align: center;">
                                    <span style="color: {{ $sColor[0] }}; background: {{ $sColor[1] }}; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700;">
                                        {{ ucwords(str_replace('_', ' ', $appt->status)) }}
                                    </span>
                                </td>
                                <td style="padding: 14px 24px; text-align: end;">
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" style="font-size: 12px; color: var(--agri-primary); text-decoration: none; font-weight: 600;">View <i class="fas fa-external-link-alt" style="font-size: 10px;"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- ═══════ RIGHT COLUMN ═══════ --}}
        <div class="col-lg-4">

            {{-- Approval Actions --}}
            <div class="card-agri mb-4" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-shield-alt"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Admin Actions</h6>
                </div>

                {{-- Approve --}}
                @if($approval !== 'approved')
                <form action="{{ route('admin.experts.approve', $expert->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Admin Notes (optional)</label>
                        <textarea name="admin_notes" rows="2" class="form-agri" placeholder="Notes to log with approval...">{{ old('admin_notes', $profile?->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary w-100" style="justify-content: center; gap: 8px;">
                        <i class="fas fa-check-circle"></i> Approve Expert
                    </button>
                </form>
                @endif

                {{-- Reject --}}
                @if($approval !== 'rejected')
                <form action="{{ route('admin.experts.reject', $expert->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Rejection Reason <span style="color: #DC2626;">*</span></label>
                        <textarea name="admin_notes" rows="2" class="form-agri" placeholder="Reason for rejection..." required></textarea>
                    </div>
                    <button type="submit" class="btn-agri w-100"
                            style="justify-content: center; gap: 8px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;"
                            onclick="return confirm('Reject this expert?')">
                        <i class="fas fa-times-circle"></i> Reject Expert
                    </button>
                </form>
                @endif

                {{-- Suspend --}}
                @if($approval === 'approved')
                <form action="{{ route('admin.experts.suspend', $expert->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Suspension Reason <span style="color: #DC2626;">*</span></label>
                        <textarea name="admin_notes" rows="2" class="form-agri" placeholder="Reason for suspension..." required></textarea>
                    </div>
                    <button type="submit" class="btn-agri w-100"
                            style="justify-content: center; gap: 8px; background: #F3F4F6; color: #374151; border: 1px solid #D1D5DB; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;"
                            onclick="return confirm('Suspend this expert?')">
                        <i class="fas fa-pause-circle"></i> Suspend Expert
                    </button>
                </form>
                @endif

                {{-- Toggle Availability --}}
                <form action="{{ route('admin.experts.toggle', $expert->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-agri w-100"
                            style="justify-content: center; gap: 8px; background: {{ $expert->is_available ? '#FEF3C7' : '#D1FAE5' }}; color: {{ $expert->is_available ? '#92400E' : '#065F46' }}; border: 1px solid {{ $expert->is_available ? '#FCD34D' : '#6EE7B7' }}; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-{{ $expert->is_available ? 'toggle-on' : 'toggle-off' }}"></i>
                        {{ $expert->is_available ? 'Mark Unavailable' : 'Mark Available' }}
                    </button>
                </form>
            </div>

            {{-- Meta Card --}}
            <div class="card-agri" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-bg); color: var(--agri-text-muted); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fas fa-info-circle"></i></div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Expert Meta</h6>
                </div>
                @php
                    $meta = [
                        ['Expert ID',         '#' . $expert->id],
                        ['Total Bookings',    $expert->appointments_count],
                        ['Availability',      $expert->is_available ? 'Available' : 'Unavailable'],
                        ['Approved At',       $profile?->approved_at?->format('M j, Y') ?? '—'],
                        ['Last Updated',      $expert->updated_at?->diffForHumans() ?? '—'],
                    ];
                @endphp
                @foreach($meta as [$label, $value])
                <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid var(--agri-border);">
                    <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">{{ $label }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); text-align: right; max-width: 58%; word-break: break-word;">{{ $value }}</span>
                </div>
                @endforeach
                @if($profile?->admin_notes)
                <div style="margin-top: 16px; background: #FEF3C7; border-radius: 12px; padding: 14px; border: 1px solid #FCD34D;">
                    <p style="margin: 0 0 6px 0; font-size: 11px; font-weight: 700; color: #92400E; text-transform: uppercase;">Admin Notes</p>
                    <p style="margin: 0; font-size: 13px; color: #78350F; line-height: 1.5;">{{ $profile->admin_notes }}</p>
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
