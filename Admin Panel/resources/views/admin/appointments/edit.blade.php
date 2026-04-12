@extends('layouts.app')

@section('title', 'Edit Appointment #' . $appointment->id)

@section('content')
@php
    use App\Models\Appointment;
    $currency = config('plantix.currency_symbol', 'PKR');
    $expert = optional(optional($appointment->expert)->user);
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
                <a href="{{ route('admin.appointments.show', $appointment->id) }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 13px; font-weight: 600;">
                    #{{ $appointment->id }}
                </a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 13px; font-weight: 600;">Edit</span>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                <h1 style="font-size: 26px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                    Edit Appointment <span style="color: var(--agri-primary);">#{{ $appointment->id }}</span>
                </h1>
                <div style="display: inline-flex; align-items: center; gap: 8px; color: {{ $appointment->type === 'physical' ? '#059669' : '#2563EB' }}; background: {{ $appointment->type === 'physical' ? '#D1FAE5' : '#DBEAFE' }}; padding: 6px 16px; border-radius: 12px; font-size: 12px; font-weight: 700; border: 1px solid {{ $appointment->type === 'physical' ? '#059669' : '#2563EB' }}40; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ strtoupper($appointment->type_label) }}
                </div>
            </div>
            <p style="color: var(--agri-text-muted); margin: 8px 0 0 0; font-size: 14px;">
                Created {{ $appointment->created_at?->diffForHumans() ?? '—' }}
            </p>
        </div>
        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert mb-4" role="alert" style="border-radius: 14px; border: none; background: #D1FAE5; color: #065F46; font-weight: 700; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;"><i class="fas fa-check-circle" style="font-size: 18px;"></i> {{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="opacity: 0.5;"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert mb-4" style="border-radius: 14px; border: none; background: #FEE2E2; color: #991B1B; font-weight: 700; padding: 18px 24px;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
            <ul style="margin: 8px 0 0 0; padding-left: 20px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.appointments.update', $appointment->id) }}">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ════════════════════ LEFT COLUMN ════════════════════ --}}
            <div class="col-lg-8">

                {{-- ── Appointment Details ── --}}
                <div class="card-agri mb-4" style="padding: 32px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                        <div style="width: 40px; height: 40px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Appointment Details</h5>
                            <p style="margin: 0; font-size: 13px; color: var(--agri-text-muted);">Update session information</p>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Expert Assignment --}}
                        <div class="col-12">
                            <label class="agri-label">Assigned Expert</label>
                            <select name="expert_id" class="form-agri">
                                <option value="">— Not assigned —</option>
                                @foreach($experts as $exp)
                                    <option value="{{ $exp->id }}" @selected($appointment->expert_id === $exp->id)>
                                        {{ $exp->user->name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Schedule --}}
                        <div class="col-md-6">
                            <label class="agri-label">Scheduled Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-agri" value="{{ $appointment->scheduled_at ? $appointment->scheduled_at->format('Y-m-d\TH:i') : '' }}">
                            @error('scheduled_at')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Fee --}}
                        <div class="col-md-6">
                            <label class="agri-label">Consultation Fee ({{ $currency }})</label>
                            <input type="number" name="fee" class="form-agri" value="{{ old('fee', $appointment->fee ?? '') }}" step="0.01" min="0">
                            @error('fee')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Topic --}}
                        <div class="col-12">
                            <label class="agri-label">Consultation Topic</label>
                            <input type="text" name="topic" class="form-agri" value="{{ old('topic', $appointment->topic ?? '') }}" placeholder="e.g., Crop disease consultation">
                            @error('topic')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Meeting Link (Online) --}}
                        @if($appointment->type === 'online')
                        <div class="col-12">
                            <label class="agri-label">Meeting Link <span style="color: #DC2626;">*</span></label>
                            <input type="url" name="meeting_link" class="form-agri @error('meeting_link') is-invalid @enderror" value="{{ old('meeting_link', $appointment->meeting_link ?? '') }}" placeholder="https://meet.google.com/..." required>
                            <small style="color: var(--agri-text-muted); display: block; margin-top: 6px;">Required for online consultations</small>
                            @error('meeting_link')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>
                        @endif

                        {{-- Location (Physical) --}}
                        @if($appointment->type === 'physical')
                        <div class="col-12">
                            <label class="agri-label">Physical Location <span style="color: #DC2626;">*</span></label>
                            <input type="text" name="location" class="form-agri @error('location') is-invalid @enderror" value="{{ old('location', $appointment->location ?? '') }}" placeholder="e.g., Farm location or clinic address" required>
                            <small style="color: var(--agri-text-muted); display: block; margin-top: 6px;">Required for physical consultations</small>
                            @error('location')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>
                        @endif

                        {{-- Notes --}}
                        <div class="col-12">
                            <label class="agri-label">Admin Notes</label>
                            <textarea name="notes" class="form-agri" rows="4" placeholder="Additional notes for internal reference...">{{ old('notes', $appointment->notes ?? '') }}</textarea>
                            @error('notes')
                                <small style="color: #DC2626; display: block; margin-top: 6px;">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ── Farmer Info (Read-only) ── --}}
                <div class="card-agri mb-4" style="padding: 32px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--agri-border); padding-bottom: 18px;">
                        <div style="width: 40px; height: 40px; background: #F3F4F6; color: #6B7280; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Customer Information</h5>
                            <p style="margin: 0; font-size: 13px; color: var(--agri-text-muted);">Read-only reference</p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;">Customer Name</p>
                            <p style="margin: 0; font-size: 15px; font-weight: 600; color: var(--agri-text-heading);">{{ $appointment->user->name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;">Email</p>
                            <p style="margin: 0; font-size: 14px; color: var(--agri-text-muted);">{{ $appointment->user->email ?? '—' }}</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ════════════════════ RIGHT COLUMN ════════════════════ --}}
            <div class="col-lg-4">

                {{-- ── Status Info ── --}}
                <div class="card-agri mb-4" style="padding: 32px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px;">
                        <div style="width: 40px; height: 40px; background: #F3F4F6; color: #6B7280; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h5 style="margin: 0; font-weight: 700; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Status (Read-only)</h5>
                    </div>
                    <div style="background: var(--agri-bg); border-radius: 12px; padding: 16px;">
                        <p style="margin: 0 0 8px 0; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px;">Current Status</p>
                        <p style="margin: 0; font-size: 14px; font-weight: 700; color: var(--agri-primary);">{{ strtoupper(str_replace('_', ' ', $appointment->status)) }}</p>
                        <p style="margin: 6px 0 0 0; font-size: 12px; color: var(--agri-text-muted);">Use status actions to modify appointment flow</p>
                    </div>
                </div>

                {{-- ── Save Actions ── --}}
                <div class="card-agri" style="padding: 24px; background: var(--agri-primary-light); border: 2px solid var(--agri-primary);">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; padding: 12px 16px; font-weight: 700;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Save Changes
                        </button>
                        <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn-agri btn-agri-outline" style="width: 100%; padding: 12px 16px; text-decoration: none; text-align: center; font-weight: 700;">
                            <i class="fas fa-times" style="margin-right: 8px;"></i> Cancel
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div>

@endsection
