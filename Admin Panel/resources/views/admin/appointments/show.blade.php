@extends('layouts.app')

@section('title', 'Appointment #' . $appointment->id)

@section('content')
@php
    use App\Models\Appointment;

    $statusMap = [
        Appointment::STATUS_DRAFT                    => ['#6B7280', '#F3F4F6', 'Draft'],
        Appointment::STATUS_PENDING_PAYMENT          => ['#D97706', '#FEF3C7', 'Pending Payment'],
        Appointment::STATUS_PAYMENT_FAILED           => ['#DC2626', '#FEE2E2', 'Payment Failed'],
        Appointment::STATUS_PENDING_EXPERT_APPROVAL  => ['#7C3AED', '#EDE9FE', 'Awaiting Expert'],
        Appointment::STATUS_CONFIRMED                => ['#059669', '#D1FAE5', 'Confirmed'],
        Appointment::STATUS_RESCHEDULE_REQUESTED     => ['#2563EB', '#DBEAFE', 'Reschedule Requested'],
        Appointment::STATUS_REJECTED                 => ['#B91C1C', '#FEE2E2', 'Rejected'],
        Appointment::STATUS_COMPLETED                => ['#0D9488', '#CCFBF1', 'Completed'],
        Appointment::STATUS_CANCELLED                => ['#9CA3AF', '#F9FAFB', 'Cancelled'],
    ];
    $s = $statusMap[$appointment->status] ?? ['#4B5563', '#F3F4F6', ucwords(str_replace('_',' ',$appointment->status))];

    $paymentMap = [
        'succeeded'               => ['#059669', '#D1FAE5', 'Succeeded'],
        'requires_payment_method' => ['#DC2626', '#FEE2E2', 'Failed'],
        'processing'              => ['#D97706', '#FEF3C7', 'Processing'],
        'requires_action'         => ['#7C3AED', '#EDE9FE', 'Requires Action'],
        'canceled'                => ['#9CA3AF', '#F9FAFB', 'Canceled'],
    ];
    $ps = $paymentMap[$appointment->stripe_payment_status ?? ''] ?? ['#9CA3AF', '#F9FAFB', '—'];

    $currency = config('plantix.currency_symbol', 'PKR');
    $expert   = optional(optional($appointment->expert)->user);
@endphp

<div class="container-fluid" style="padding-top: 24px; padding-bottom: 48px;">

    {{-- Header breadcrumb --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <a href="{{ route('admin.appointments.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-calendar-check"></i> Appointments
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">#{{ $appointment->id }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                    Appointment <span style="color: var(--agri-primary);">#{{ $appointment->id }}</span>
                </h1>
                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $s[0] }}; background: {{ $s[1] }}; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; border: 1px solid {{ $s[0] }}40; text-transform: uppercase; letter-spacing: 0.5px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $s[0] }}; box-shadow: 0 0 0 2px {{ $s[0] }}30;"></span>
                    {{ $s[2] }}
                </div>
            </div>
            <p style="color: var(--agri-text-muted); margin: 8px 0 0 0; font-size: 14px;">
                Booked {{ $appointment->created_at?->diffForHumans() ?? '—' }}
            </p>
        </div>
        <a href="{{ route('admin.appointments.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert mb-4" role="alert" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="opacity: 0.5;"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert mb-4" role="alert" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-exclamation-circle" style="font-size: 18px;"></i> {{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="opacity: 0.5;"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- ════════════════════ LEFT COLUMN ════════════════════ --}}
        <div class="col-lg-8">

            {{-- ── Appointment Overview ── --}}
            <div class="card-agri mb-4" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Appointment Details</h5>
                        <p style="margin: 0; font-size: 13px; color: var(--agri-text-muted);">Session scheduling and participant information</p>
                    </div>
                </div>
                <div class="row g-4">
                    {{-- Farmer --}}
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 12px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-user" style="margin-right: 6px; color: var(--agri-primary);"></i>Farmer / Customer</p>
                            <p style="margin: 0; font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $appointment->user->name ?? '—' }}</p>
                            <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $appointment->user->email ?? '' }}</p>
                            @if($appointment->user->phone ?? null)
                                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);"><i class="fas fa-phone" style="font-size: 11px; margin-right: 4px;"></i>{{ $appointment->user->phone }}</p>
                            @endif
                        </div>
                    </div>
                    {{-- Expert --}}
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 12px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-user-tie" style="margin-right: 6px; color: var(--agri-secondary);"></i>Agricultural Expert</p>
                            @if($appointment->expert)
                                <p style="margin: 0; font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $expert->name ?? '—' }}</p>
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $appointment->expert->specialty ?? '' }}</p>
                                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $expert->email ?? '' }}</p>
                            @else
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-muted); font-style: italic;">Not yet assigned</p>
                            @endif
                        </div>
                    </div>
                    {{-- Schedule --}}
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 12px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-clock" style="margin-right: 6px; color: var(--agri-primary);"></i>Scheduled Time</p>
                            @if($appointment->scheduled_at)
                                <p style="margin: 0; font-size: 16px; font-weight: 800; color: var(--agri-text-heading);">{{ $appointment->scheduled_at->format('l, F j, Y') }}</p>
                                <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">
                                    {{ $appointment->scheduled_at->format('g:i A') }}
                                    @if($appointment->end_time) – {{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }} @endif
                                </p>
                            @else
                                <p style="margin: 0; font-size: 14px; color: var(--agri-text-muted); font-style: italic;">Not scheduled yet</p>
                            @endif
                        </div>
                    </div>
                    {{-- Consultation Fee --}}
                    <div class="col-md-6">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 12px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-money-bill-wave" style="margin-right: 6px; color: var(--agri-success);"></i>Consultation Fee</p>
                            <p style="margin: 0; font-size: 24px; font-weight: 900; color: var(--agri-primary-dark);">{{ $currency }} {{ number_format($appointment->fee ?? 0, 2) }}</p>
                            @if($appointment->is_refunded)
                                <div style="margin-top: 6px; display: inline-flex; align-items: center; gap: 6px; color: #DC2626; background: #FEE2E2; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; border: 1px solid #DC262620;">
                                    <i class="fas fa-undo"></i> Refunded {{ $currency }} {{ number_format($appointment->refund_amount ?? $appointment->fee, 2) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    {{-- Topic --}}
                    @if($appointment->topic)
                    <div class="col-12">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-tag" style="margin-right: 6px; color: var(--agri-primary);"></i>Consultation Topic</p>
                            <p style="margin: 0; font-size: 15px; font-weight: 600; color: var(--agri-text-heading);">{{ $appointment->topic }}</p>
                        </div>
                    </div>
                    @endif
                    {{-- Notes --}}
                    @if($appointment->notes)
                    <div class="col-12">
                        <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-sticky-note" style="margin-right: 6px; color: var(--agri-primary);"></i>Customer Notes</p>
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-main); line-height: 1.6;">{{ $appointment->notes }}</p>
                        </div>
                    </div>
                    @endif
                    {{-- Meeting Link --}}
                    @if($appointment->meeting_link)
                    <div class="col-12">
                        <div style="background: #EFF6FF; border-radius: 14px; padding: 20px; border: 1px solid #BFDBFE;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #1D4ED8; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-video" style="margin-right: 6px;"></i>Meeting Link</p>
                            <a href="{{ $appointment->meeting_link }}" target="_blank" style="font-size: 14px; color: #2563EB; word-break: break-all;">{{ $appointment->meeting_link }}</a>
                        </div>
                    </div>
                    @endif
                    {{-- Cancellation / Rejection info --}}
                    @if($appointment->cancellation_reason)
                    <div class="col-12">
                        <div style="background: #FEF2F2; border-radius: 14px; padding: 20px; border: 1px solid #FECACA;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #B91C1C; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-ban" style="margin-right: 6px;"></i>Cancellation Reason</p>
                            <p style="margin: 0; font-size: 14px; color: #7F1D1D; line-height: 1.6;">{{ $appointment->cancellation_reason }}</p>
                        </div>
                    </div>
                    @endif
                    @if($appointment->reject_reason)
                    <div class="col-12">
                        <div style="background: #FEF2F2; border-radius: 14px; padding: 20px; border: 1px solid #FECACA;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #B91C1C; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-times-circle" style="margin-right: 6px;"></i>Rejection Reason</p>
                            <p style="margin: 0; font-size: 14px; color: #7F1D1D; line-height: 1.6;">{{ $appointment->reject_reason }}</p>
                        </div>
                    </div>
                    @endif
                    @if($appointment->expert_response_notes)
                    <div class="col-12">
                        <div style="background: #F0FDF4; border-radius: 14px; padding: 20px; border: 1px solid #BBF7D0;">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: #15803D; text-transform: uppercase; letter-spacing: 1px;"><i class="fas fa-comment" style="margin-right: 6px;"></i>Expert Response Notes</p>
                            <p style="margin: 0; font-size: 14px; color: #14532D; line-height: 1.6;">{{ $appointment->expert_response_notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Payment Info ── --}}
            <div class="card-agri mb-4" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: #EFF6FF; color: #2563EB; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fab fa-stripe-s"></i>
                    </div>
                    <div>
                        <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Payment Information</h5>
                        <p style="margin: 0; font-size: 13px; color: var(--agri-text-muted);">Stripe transaction and refund details</p>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Payment Intent ID</p>
                        @if($appointment->stripe_payment_intent_id)
                            <code style="font-size: 12px; background: var(--agri-bg); padding: 6px 12px; border-radius: 8px; display: block; word-break: break-all; color: var(--agri-text-heading);">{{ $appointment->stripe_payment_intent_id }}</code>
                        @else
                            <p style="margin: 0; color: var(--agri-text-muted); font-style: italic; font-size: 14px;">Not created yet</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Payment Status</p>
                        <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $ps[0] }}; background: {{ $ps[1] }}; padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 700; border: 1px solid {{ $ps[0] }}40;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $ps[0] }};"></span>
                            {{ $ps[2] }}
                        </div>
                    </div>
                    @if($appointment->is_refunded)
                    <div class="col-md-6">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Refund Amount</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 800; color: #DC2626;">{{ $currency }} {{ number_format($appointment->refund_amount ?? $appointment->fee, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Refund ID</p>
                        @if($appointment->stripe_refund_id)
                            <code style="font-size: 12px; background: var(--agri-bg); padding: 6px 12px; border-radius: 8px; display: block; word-break: break-all; color: var(--agri-text-heading);">{{ $appointment->stripe_refund_id }}</code>
                        @else
                            <p style="margin: 0; color: var(--agri-text-muted); font-size: 14px;">—</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p style="font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Refunded At</p>
                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">{{ $appointment->refunded_at?->format('M j, Y g:i A') ?? '—' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Status History ── --}}
            @if($appointment->statusHistory && $appointment->statusHistory->count())
            <div class="card-agri mb-4" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: #EDE9FE; color: #7C3AED; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Status Timeline</h5>
                </div>
                <div style="position: relative; padding-left: 28px;">
                    <div style="position: absolute; left: 7px; top: 0; bottom: 0; width: 2px; background: var(--agri-border);"></div>
                    @foreach($appointment->statusHistory->sortByDesc('changed_at') as $history)
                    <div style="position: relative; margin-bottom: 24px;">
                        <div style="position: absolute; left: -25px; top: 4px; width: 10px; height: 10px; border-radius: 50%; background: var(--agri-primary); border: 2px solid white; box-shadow: 0 0 0 2px var(--agri-primary);"></div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); background: var(--agri-bg); padding: 2px 10px; border-radius: 6px;">{{ str_replace('_', ' ', strtoupper($history->from_status)) }}</span>
                                    <i class="fas fa-arrow-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                                    <span style="font-size: 12px; font-weight: 700; color: var(--agri-primary); background: var(--agri-primary-light); padding: 2px 10px; border-radius: 6px;">{{ str_replace('_', ' ', strtoupper($history->to_status)) }}</span>
                                </div>
                                @if($history->notes)
                                    <p style="margin: 6px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">{{ $history->notes }}</p>
                                @endif
                            </div>
                            <div style="text-align: right;">
                                <p style="margin: 0; font-size: 12px; color: var(--agri-text-muted);">{{ optional($history->changed_at)->diffForHumans() ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: var(--agri-text-muted);">{{ optional($history->changed_at)->format('M j, Y g:i A') ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ── Audit Logs ── --}}
            @if($appointment->logs && $appointment->logs->count())
            <div class="card-agri mb-4" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: #FEF3C7; color: #D97706; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Audit Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Action</th>
                                <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Performed By</th>
                                <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none; text-align: right;">When</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointment->logs->sortByDesc('created_at') as $log)
                            <tr style="border-bottom: 1px solid var(--agri-border);">
                                <td style="padding: 14px 16px;">
                                    <span style="font-size: 12px; font-weight: 700; background: var(--agri-bg); color: var(--agri-primary-dark); padding: 4px 10px; border-radius: 8px;">{{ str_replace('_', ' ', strtoupper($log->action)) }}</span>
                                    @if($log->context && is_array($log->context) && isset($log->context['notes']))
                                        <p style="margin: 4px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">{{ $log->context['notes'] }}</p>
                                    @endif
                                </td>
                                <td style="padding: 14px 16px; font-size: 13px; color: var(--agri-text-main);">
                                    {{ optional($log->user)->name ?? 'System' }}
                                </td>
                                <td style="padding: 14px 16px; font-size: 12px; color: var(--agri-text-muted); text-align: right;">
                                    <span title="{{ $log->created_at->format('M j, Y g:i:s A') }}">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- ── Reschedule Requests ── --}}
            @if($appointment->reschedules && $appointment->reschedules->count())
            <div class="card-agri" style="padding: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                    <div style="width: 40px; height: 40px; background: #DBEAFE; color: #2563EB; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Reschedule Requests</h5>
                </div>
                @foreach($appointment->reschedules->sortByDesc('created_at') as $rs)
                <div style="background: var(--agri-bg); border-radius: 14px; padding: 20px; margin-bottom: 12px;">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Original</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">{{ optional($rs->original_scheduled_at)->format('M j, Y g:i A') ?? '—' }}</p>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-arrow-right" style="color: var(--agri-text-muted);"></i>
                        </div>
                        <div class="col-md-5">
                            <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: var(--agri-primary); text-transform: uppercase;">Proposed</p>
                            <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--agri-primary-dark);">{{ optional($rs->proposed_scheduled_at)->format('M j, Y g:i A') ?? '—' }}</p>
                        </div>
                        @if($rs->reason)
                        <div class="col-12">
                            <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase;">Reason</p>
                            <p style="margin: 0; font-size: 13px; color: var(--agri-text-main);">{{ $rs->reason }}</p>
                        </div>
                        @endif
                    </div>
                    <p style="margin: 12px 0 0 0; font-size: 11px; color: var(--agri-text-muted);">{{ $rs->created_at->diffForHumans() }} · Status: <strong>{{ ucfirst($rs->status ?? 'pending') }}</strong></p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- ════════════════════ RIGHT COLUMN ════════════════════ --}}
        <div class="col-lg-4">

            {{-- ── Quick Actions ── --}}
            <div class="card-agri mb-4" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Admin Actions</h6>
                </div>

                {{-- Confirm --}}
                @if(in_array($appointment->status, [
                    \App\Models\Appointment::STATUS_PENDING_EXPERT_APPROVAL,
                    \App\Models\Appointment::STATUS_RESCHEDULE_REQUESTED,
                ]))
                <form action="{{ route('admin.appointments.confirm', $appointment->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 6px; display: block;">Admin Notes (optional)</label>
                        <textarea name="notes" rows="2" class="form-agri" placeholder="Notes for customer..."></textarea>
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary w-100" style="justify-content: center; gap: 8px;">
                        <i class="fas fa-check-circle"></i> Force Confirm
                    </button>
                </form>
                @endif

                {{-- Complete --}}
                @if($appointment->status === \App\Models\Appointment::STATUS_CONFIRMED)
                <form action="{{ route('admin.appointments.complete', $appointment->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn-agri w-100" onclick="return confirm('Mark as completed?')"
                        style="justify-content: center; gap: 8px; background: #D1FAE5; color: #065F46; border: 1px solid #6EE7B7; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                        <i class="fas fa-flag-checkered"></i> Mark as Completed
                    </button>
                </form>
                @endif

                {{-- Refund --}}
                @if(($appointment->stripe_payment_status ?? '') === 'succeeded' && ! $appointment->is_refunded)
                <button type="button" class="btn-agri w-100 mb-3"
                        data-bs-toggle="modal" data-bs-target="#refundModal"
                        style="justify-content: center; gap: 8px; background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    <i class="fas fa-undo"></i> Issue Refund
                </button>
                @endif

                {{-- Reassign Expert --}}
                <button type="button" class="btn-agri w-100 mb-3"
                        data-bs-toggle="modal" data-bs-target="#reassignModal"
                        style="justify-content: center; gap: 8px; background: #EDE9FE; color: #5B21B6; border: 1px solid #C4B5FD; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    <i class="fas fa-user-edit"></i> Reassign Expert
                </button>

                {{-- Cancel --}}
                @if(! in_array($appointment->status, [
                    \App\Models\Appointment::STATUS_COMPLETED,
                    \App\Models\Appointment::STATUS_CANCELLED,
                    \App\Models\Appointment::STATUS_REJECTED,
                ]))
                <button type="button" class="btn-agri w-100"
                        data-bs-toggle="modal" data-bs-target="#cancelModal"
                        style="justify-content: center; gap: 8px; background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; padding: 12px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    <i class="fas fa-ban"></i> Cancel Appointment
                </button>
                @endif
            </div>

            {{-- ── Appointment Meta ── --}}
            <div class="card-agri" style="padding: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; border-bottom: 1px solid var(--agri-border); padding-bottom: 16px;">
                    <div style="width: 36px; height: 36px; background: var(--agri-bg); color: var(--agri-text-muted); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Appointment Meta</h6>
                </div>
                @php
                    $metaRows = [
                        ['Booking ID',    '#' . $appointment->id],
                        ['Created',       $appointment->created_at?->format('M j, Y g:i A') ?? '—'],
                        ['Last Updated',  $appointment->updated_at?->diffForHumans() ?? '—'],
                        ['Reminder Sent', $appointment->reminder_sent_at ? $appointment->reminder_sent_at->format('M j, Y g:i A') : 'Not yet sent'],
                    ];
                @endphp
                @foreach($metaRows as [$label, $value])
                <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid var(--agri-border);">
                    <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">{{ $label }}</span>
                    <span style="font-size: 13px; font-weight: 600; color: var(--agri-text-heading); text-align: right; max-width: 58%; word-break: break-word;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════ MODALS ════════════════════════════════ --}}

{{-- Refund Modal --}}
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="padding: 28px 32px 0; border: none;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; background: #FEE2E2; color: #DC2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-undo"></i></div>
                    <div>
                        <h5 class="modal-title" style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 18px;">Issue Refund</h5>
                        <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">Appointment #{{ $appointment->id }} · {{ $currency }} {{ number_format($appointment->fee ?? 0, 2) }} paid</p>
                    </div>
                </div>
            </div>
            <form action="{{ route('admin.appointments.refund', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px 32px;">
                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Refund Type</label>
                        <div style="display: flex; gap: 12px;">
                            <label style="flex: 1; display: flex; align-items: center; gap: 8px; cursor: pointer; background: var(--agri-bg); border: 2px solid var(--agri-border); border-radius: 12px; padding: 12px 16px; font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">
                                <input type="radio" name="refund_type" value="full" checked onchange="togglePartial(false)"> Full
                            </label>
                            <label style="flex: 1; display: flex; align-items: center; gap: 8px; cursor: pointer; background: var(--agri-bg); border: 2px solid var(--agri-border); border-radius: 12px; padding: 12px 16px; font-size: 14px; font-weight: 600; color: var(--agri-text-heading);">
                                <input type="radio" name="refund_type" value="partial" onchange="togglePartial(true)"> Partial
                            </label>
                        </div>
                    </div>
                    <div id="partialAmountBlock" style="margin-bottom: 20px; display: none;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Amount ({{ $currency }})</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-weight: 700; font-size: 14px;">{{ $currency }}</span>
                            <input type="number" name="amount" step="0.01" min="1" max="{{ $appointment->fee ?? 0 }}"
                                   class="form-agri" style="padding-left: 60px;" placeholder="0.00" id="partialAmountInput">
                        </div>
                        <p style="margin: 5px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">Max: {{ $currency }} {{ number_format($appointment->fee ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Reason <span style="color: #DC2626;">*</span></label>
                        <textarea name="reason" rows="3" class="form-agri" placeholder="Reason for the refund..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 0 32px 28px; border: none; gap: 12px;">
                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; justify-content: center;">Cancel</button>
                    <button type="submit" style="flex: 1; justify-content: center; background: #DC2626; color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-undo"></i> Confirm Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reassign Modal --}}
<div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="padding: 28px 32px 0; border: none;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; background: #EDE9FE; color: #7C3AED; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-user-edit"></i></div>
                    <div>
                        <h5 class="modal-title" style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 18px;">Reassign Expert</h5>
                        <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">Current: {{ $expert->name ?? 'Unassigned' }}</p>
                    </div>
                </div>
            </div>
            <form action="{{ route('admin.appointments.reassign', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px 32px;">
                    <div style="margin-bottom: 20px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">New Expert <span style="color: #DC2626;">*</span></label>
                        <div style="position: relative;">
                            <i class="fas fa-user-tie" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                            <select name="expert_id" class="form-agri" style="padding-left: 40px;" required>
                                <option value="">— Select an Expert —</option>
                                @foreach($experts ?? [] as $exp)
                                    <option value="{{ $exp->id }}" @selected($exp->id == $appointment->expert_id)>
                                        {{ optional($exp->user)->name }} — {{ $exp->specialty ?? 'General' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Reason <span style="color: #DC2626;">*</span></label>
                        <textarea name="reason" rows="3" class="form-agri" placeholder="Reason for reassigning..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 0 32px 28px; border: none; gap: 12px;">
                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; justify-content: center;">Cancel</button>
                    <button type="submit" style="flex: 1; justify-content: center; background: #7C3AED; color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-user-edit"></i> Reassign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.15);">
            <div class="modal-header" style="padding: 28px 32px 0; border: none;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 44px; height: 44px; background: #FEE2E2; color: #DC2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"><i class="fas fa-ban"></i></div>
                    <div>
                        <h5 class="modal-title" style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 18px;">Cancel Appointment</h5>
                        <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--agri-text-muted);">Customer and expert will be notified.</p>
                    </div>
                </div>
            </div>
            <form action="{{ route('admin.appointments.cancel', $appointment->id) }}" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px 32px;">
                    <div style="margin-bottom: 16px;">
                        <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block;">Cancellation Reason <span style="color: #DC2626;">*</span></label>
                        <textarea name="reason" rows="4" class="form-agri" placeholder="Explain why this appointment is being cancelled..." required></textarea>
                    </div>
                    @if(($appointment->stripe_payment_status ?? '') === 'succeeded' && ! $appointment->is_refunded)
                    <div style="background: #FEF3C7; border-radius: 12px; padding: 16px; border: 1px solid #FCD34D; display: flex; gap: 10px; align-items: flex-start;">
                        <i class="fas fa-exclamation-triangle" style="color: #D97706; margin-top: 2px; flex-shrink: 0;"></i>
                        <p style="margin: 0; font-size: 13px; color: #92400E; font-weight: 600;">A full refund will be automatically issued since the customer has already paid.</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer" style="padding: 0 32px 28px; border: none; gap: 12px;">
                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; justify-content: center;">Keep It</button>
                    <button type="submit" style="flex: 1; justify-content: center; background: #DC2626; color: white; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-ban"></i> Cancel Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePartial(show) {
    const block = document.getElementById('partialAmountBlock');
    const input = document.getElementById('partialAmountInput');
    block.style.display = show ? 'block' : 'none';
    input.required = show;
}
</script>
@endpush
@endsection
