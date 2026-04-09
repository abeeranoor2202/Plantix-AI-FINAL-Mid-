@extends('layouts.app')

@section('title', 'Disease Report #' . $report->id)

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <a href="{{ route('admin.ai.disease-reports') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Disease Reports</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Report #{{ $report->id }}</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Disease Report #{{ $report->id }}</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Validate AI diagnosis, refine disease labels, and publish treatment guidance.</p>
        </div>
        <a href="{{ route('admin.ai.disease-reports') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Submitted Image</div>
                <div style="padding: 20px; text-align: center;">
                    @if($report->image_url)
                        <img src="{{ $report->image_url }}" alt="Disease Image" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 14px; border: 1px solid var(--agri-border);">
                    @else
                        <div style="padding: 40px 0; color: var(--agri-text-muted);">
                            <i class="fas fa-image" style="font-size: 36px; opacity: 0.35;"></i>
                            <p style="margin: 10px 0 0 0; font-weight: 600;">No image uploaded.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri h-100" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Report Details</div>
                <div style="padding: 20px; display: grid; gap: 10px;">
                    @php
                        $statusStyles = [
                            'verified' => ['#d1fae5', '#065f46'],
                            'pending' => ['#fef3c7', '#92400e'],
                            'processed' => ['#dbeafe', '#1e3a8a'],
                        ];
                        $status = $report->status ?? 'pending';
                        $ss = $statusStyles[$status] ?? ['#f3f4f6', '#374151'];
                    @endphp
                    <div><strong>User:</strong> {{ $report->user->name ?? '—' }}</div>
                    <div><strong>Crop:</strong> {{ $report->crop_name }}</div>
                    <div><strong>Disease:</strong> {{ $report->detected_disease ?? 'Undetected' }}</div>
                    <div><strong>Confidence:</strong> {{ $report->confidence_percent }}</div>
                    <div>
                        <strong>Status:</strong>
                        <span style="background: {{ $ss[0] }}; color: {{ $ss[1] }}; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; margin-left: 6px; text-transform: uppercase;">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    <div><strong>Description:</strong></div>
                    <div style="font-size: 13px; color: var(--agri-text-muted); line-height: 1.6;">{{ $report->description ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri h-100" style="padding: 0; overflow: hidden;">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">Admin Verification</div>
                <div style="padding: 20px;">
                    <form method="POST" action="{{ route('admin.ai.disease-reports.assign', $report->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Verified Disease Name</label>
                            <input type="text" name="verified_disease" class="form-agri"
                                   value="{{ $report->admin_verified_disease ?? $report->detected_disease }}"
                                   placeholder="e.g. wheat_rust">
                        </div>
                        <div class="mb-3">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Admin Notes</label>
                            <textarea name="admin_notes" class="form-agri" rows="4" placeholder="Optional notes for the farmer">{{ $report->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; justify-content: center;">
                            <i class="fas fa-check"></i> Save Verification
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($report->suggestion)
    <div class="card-agri mt-4" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--agri-border); font-size: 14px; font-weight: 700; color: var(--agri-text-heading);">AI Treatment Suggestion</div>
        <div style="padding: 20px;">
            <div class="row g-3">
                <div class="col-md-4">
                    <h6 style="font-size: 13px; font-weight: 800; color: #059669; text-transform: uppercase;"><i class="fas fa-leaf me-1"></i> Organic Treatment</h6>
                    <p style="margin: 0; font-size: 13px; color: var(--agri-text-main);">{{ $report->suggestion->organic_treatment }}</p>
                </div>
                <div class="col-md-4">
                    <h6 style="font-size: 13px; font-weight: 800; color: #1d4ed8; text-transform: uppercase;"><i class="fas fa-flask me-1"></i> Chemical Treatment</h6>
                    <p style="margin: 0; font-size: 13px; color: var(--agri-text-main);">{{ $report->suggestion->chemical_treatment }}</p>
                </div>
                <div class="col-md-4">
                    <h6 style="font-size: 13px; font-weight: 800; color: #b45309; text-transform: uppercase;"><i class="fas fa-shield-alt me-1"></i> Prevention</h6>
                    <p style="margin: 0; font-size: 13px; color: var(--agri-text-main);">{{ $report->suggestion->prevention_tips }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
