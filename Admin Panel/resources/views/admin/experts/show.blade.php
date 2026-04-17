@extends('layouts.app')

@section('title', 'Expert: ' . ($expert->user->name ?? 'Unknown'))

@section('content')
@php
    $profile = $expert->profile;
    $status = $expert->status ?? 'pending';
    $statusMap = [
        'approved' => ['#059669', '#D1FAE5', 'Approved'],
        'pending' => ['#D97706', '#FEF3C7', 'Pending'],
        'under_review' => ['#0284C7', '#E0F2FE', 'Under Review'],
        'rejected' => ['#DC2626', '#FEE2E2', 'Rejected'],
        'suspended' => ['#6B7280', '#F3F4F6', 'Suspended'],
        'inactive' => ['#374151', '#E5E7EB', 'Inactive'],
    ];
    $statusBadge = $statusMap[$status] ?? ['#9CA3AF', '#F9FAFB', ucfirst((string) $status)];
    $currency = config('plantix.currency_symbol', 'PKR');
    $placeholderImage = asset('images/placeholder.png');

    $imagePath = $expert->profile_image ?? null;
    $profileImage = $imagePath
        ? (filter_var($imagePath, FILTER_VALIDATE_URL) ? $imagePath : asset('storage/' . ltrim($imagePath, '/')))
        : $placeholderImage;

    $displayAddress = collect([$profile?->city, $profile?->country])->filter()->implode(', ');
@endphp

<div class="container-fluid" style="padding-top: 24px;">

    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Experts</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Expert Details</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Profile</h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Comprehensive overview of account activities and details.</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('admin.experts.edit', $expert->id) }}" class="btn-agri btn-agri-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-edit"></i> Edit Expert
                </a>
                <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">
                    <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <x-alert variant="success" class="mb-4" dismissible>{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert variant="danger" class="mb-4">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div style="display: flex; gap: 24px; border-bottom: 2px solid var(--agri-border); margin-bottom: 32px; padding-bottom: 2px;">
        <span style="text-decoration: none; padding: 12px 4px; position: relative; color: var(--agri-primary); font-weight: 700; border-bottom: 3px solid var(--agri-primary);">Basic</span>
        <a href="{{ route('admin.appointments.index') }}" style="text-decoration: none; padding: 12px 4px; color: var(--agri-text-muted); font-weight: 600;">Appointments</a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card-agri" style="text-align: center; padding: 40px 24px; margin-bottom: 24px;">
                <div style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; box-shadow: 0 8px 24px rgba(0,0,0,0.1); margin: 0 auto 24px; overflow: hidden; background: var(--agri-bg); display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $profileImage }}" style="width:100%; height:100%; object-fit:cover;" onerror="this.onerror=null;this.src='{{ $placeholderImage }}';" alt="Expert Image">
                </div>

                <h3 style="font-size: 22px; font-weight: 800; color: var(--agri-text-heading); margin-bottom: 8px;">{{ $expert->user->name ?? 'Unknown Expert' }}</h3>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--agri-primary-light); color: var(--agri-primary); padding: 4px 12px; border-radius: 100px; font-size: 13px; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-user-tie"></i> Verified Expert
                </div>

                <div style="background: var(--agri-bg); border-radius: 16px; padding: 20px; text-align: left; border: 1px solid var(--agri-border); margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-envelope" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $expert->user->email ?? 'Not mentioned' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; margin-bottom: 16px;">
                        <i class="fas fa-phone-alt" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $expert->user->phone ?? 'Not mentioned' }}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600;">
                        <i class="fas fa-map-marker-alt" style="color: var(--agri-text-muted); width: 16px;"></i>
                        <span>{{ $displayAddress ?: 'No location provided' }}</span>
                    </div>
                </div>

                <div style="display:inline-flex; align-items:center; gap:8px; color: {{ $statusBadge[0] }}; background: {{ $statusBadge[1] }}; padding: 8px 14px; border-radius: 999px; font-size: 12px; font-weight: 800; border: 1px solid {{ $statusBadge[0] }}40; text-transform: uppercase; letter-spacing: 0.5px;">
                    <span style="width:6px; height:6px; border-radius:50%; background: {{ $statusBadge[0] }};"></span>
                    {{ $statusBadge[2] }}
                </div>
            </div>

            <div class="card-agri" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px;">Admin Actions</h4>

                @if($status !== 'approved')
                    <form action="{{ route('admin.experts.approve', $expert->id) }}" method="POST" style="margin-bottom: 10px;">
                        @csrf
                        <textarea name="notes" rows="2" class="form-agri" placeholder="Approval notes (optional)" style="margin-bottom: 8px;">{{ old('notes') }}</textarea>
                        <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; justify-content: center; gap: 8px;">
                            <i class="fas fa-check-circle"></i> Approve Expert
                        </button>
                    </form>
                @endif

                @if($status !== 'rejected')
                    <form action="{{ route('admin.experts.reject', $expert->id) }}" method="POST" style="margin-bottom: 10px;" onsubmit="return confirm('Reject this expert?');">
                        @csrf
                        <textarea name="reason" rows="2" class="form-agri" placeholder="Rejection reason" style="margin-bottom: 8px;" required>{{ old('reason') }}</textarea>
                        <button type="submit" class="btn-agri btn-agri-danger" style="width: 100%; justify-content: center; gap: 8px;">
                            <i class="fas fa-times-circle"></i> Reject Expert
                        </button>
                    </form>
                @endif

                @if($status === 'approved')
                    <form action="{{ route('admin.experts.suspend', $expert->id) }}" method="POST" style="margin-bottom: 10px;" onsubmit="return confirm('Suspend this expert?');">
                        @csrf
                        <textarea name="reason" rows="2" class="form-agri" placeholder="Suspension reason" style="margin-bottom: 8px;" required>{{ old('reason') }}</textarea>
                        <button type="submit" class="btn-agri btn-agri-outline" style="width: 100%; justify-content: center; gap: 8px;">
                            <i class="fas fa-pause-circle"></i> Suspend Expert
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.experts.toggle', $expert->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-agri" style="width: 100%; justify-content: center; gap: 8px; background: {{ $expert->is_available ? '#FEF3C7' : '#D1FAE5' }}; color: {{ $expert->is_available ? '#92400E' : '#065F46' }}; border: 1px solid {{ $expert->is_available ? '#FCD34D' : '#6EE7B7' }};">
                        <i class="fas fa-{{ $expert->is_available ? 'toggle-on' : 'toggle-off' }}"></i>
                        {{ $expert->is_available ? 'Mark Unavailable' : 'Mark Available' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card-agri" style="padding: 32px; margin-bottom: 24px;">
                <h4 style="font-size: 18px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 24px;">Address</h4>
                @if($displayAddress)
                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 16px; padding: 20px;">
                        <h6 style="font-weight:700; color:var(--agri-text-heading); margin-bottom:4px; line-height:1.4;">{{ $displayAddress }}</h6>
                        <p style="font-size:13px; color:var(--agri-text-muted); margin:0;">Primary expert location</p>
                    </div>
                @else
                    <div style="background:var(--agri-bg); padding:40px; border-radius:16px; text-align:center; border: 1px dashed var(--agri-border); color:var(--agri-text-muted);">
                        <i class="fas fa-map-marker-alt" style="font-size:32px; margin-bottom:12px; opacity:0.3;"></i>
                        <p style="margin:0; font-weight:600;">No shipping address found</p>
                    </div>
                @endif
            </div>

            <div class="row g-3" style="margin-bottom: 24px;">
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $expert->appointments_count ?? 0 }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Total Appointments</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $expert->specializations?->count() ?? 0 }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Specializations</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-agri" style="padding: 20px; text-align: center;">
                        <div style="font-size: 30px; font-weight: 800; color: var(--agri-primary);">{{ $expert->is_available ? 'Yes' : 'No' }}</div>
                        <div style="font-size: 13px; color: var(--agri-text-muted); margin-top: 6px;">Available</div>
                    </div>
                </div>
            </div>

            <div class="card-agri" style="padding: 24px; margin-bottom: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 16px;">Professional Details</h4>

                <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom: 16px;">
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-leaf"></i> {{ $expert->specialty ?? ($profile?->specialization ?? 'Not specified') }}
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-money-bill-wave"></i> {{ $currency }} {{ number_format((float)($expert->hourly_rate ?? 0), 2) }} / hr
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:6px; background: var(--agri-bg); color: var(--agri-text-heading); padding: 8px 12px; border-radius: 999px; font-size: 13px; font-weight: 700; border:1px solid var(--agri-border);">
                        <i class="fas fa-star" style="color:#D97706;"></i> {{ number_format((float)($expert->rating_avg ?? 0), 1) }} rating
                    </span>
                </div>

                @if($expert->bio)
                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 14px; padding: 16px; margin-bottom: 12px;">
                        <p style="margin:0; color: var(--agri-text-main); line-height: 1.7;">{{ $expert->bio }}</p>
                    </div>
                @endif

                @if($profile?->certifications)
                    <div style="background: var(--agri-bg); border: 1px solid var(--agri-border); border-radius: 14px; padding: 16px;">
                        <div style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px;">Certifications</div>
                        <p style="margin:0; color: var(--agri-text-main); line-height: 1.7;">{{ $profile->certifications }}</p>
                    </div>
                @endif
            </div>

            @if($expert->appointments && $expert->appointments->count())
            <div class="card-agri" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); font-weight: 700; color: var(--agri-primary-dark);">Recent Appointments</div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Scheduled</th>
                                <th style="padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expert->appointments->take(10) as $appt)
                                <tr style="border-bottom: 1px solid var(--agri-border);">
                                    <td style="padding: 14px 24px; font-size: 13px; font-weight: 600; color: var(--agri-text-heading);">{{ $appt->user->name ?? '—' }}</td>
                                    <td style="padding: 14px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $appt->scheduled_at?->format('M j, Y g:i A') ?? 'TBD' }}</td>
                                    <td style="padding: 14px 24px; text-align: center; font-size: 12px; font-weight: 700; color: var(--agri-primary-dark);">{{ ucwords(str_replace('_', ' ', $appt->status)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection