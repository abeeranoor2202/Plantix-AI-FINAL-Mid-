@extends('layouts.app')

@section('title', 'Disease Report #' . $report->id)

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Disease Report <span class="text-muted">#{{ $report->id }}</span></h2>
        <a href="{{ route('admin.ai.disease-reports') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- Uploaded Image --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">Submitted Image</div>
                <div class="card-body text-center">
                    @if($report->image_url)
                        <img src="{{ $report->image_url }}" alt="Disease Image"
                             class="img-fluid rounded" style="max-height:280px; object-fit:cover;">
                    @else
                        <p class="text-muted">No image uploaded.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Report Details --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">Report Details</div>
                <div class="card-body">
                    <p class="mb-1"><strong>User:</strong> {{ $report->user->name ?? '—' }}</p>
                    <p class="mb-1"><strong>Crop:</strong> {{ $report->crop_name }}</p>
                    <p class="mb-1"><strong>Disease:</strong> {{ $report->detected_disease ?? 'Undetected' }}</p>
                    <p class="mb-1"><strong>Confidence:</strong> {{ $report->confidence_percent }}</p>
                    <p class="mb-1"><strong>Status:</strong>
                        <span class="badge bg-{{ $report->status === 'verified' ? 'success' : ($report->status === 'pending' ? 'warning' : 'primary') }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </p>
                    <p class="mb-0"><strong>Description:</strong><br>
                        <span class="small text-muted">{{ $report->description ?? '—' }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Admin Override --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">Admin Assign Disease</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.ai.disease-reports.assign', $report->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Verified Disease Name</label>
                            <input type="text" name="verified_disease" class="form-control form-control-sm"
                                   value="{{ $report->admin_verified_disease ?? $report->detected_disease }}"
                                   placeholder="e.g. wheat_rust">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Admin Notes</label>
                            <textarea name="admin_notes" class="form-control form-control-sm" rows="3"
                                      placeholder="Optional expert notes for the farmer">{{ $report->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="fas fa-check me-1"></i> Save Verification
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Suggestion --}}
    @if($report->suggestion)
    <div class="card shadow-sm mt-4">
        <div class="card-header fw-semibold">AI Treatment Suggestion</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <h6 class="text-success"><i class="fas fa-leaf me-1"></i>Organic Treatment</h6>
                    <p class="small">{{ $report->suggestion->organic_treatment }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary"><i class="fas fa-flask me-1"></i>Chemical Treatment</h6>
                    <p class="small">{{ $report->suggestion->chemical_treatment }}</p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-warning"><i class="fas fa-shield-alt me-1"></i>Prevention</h6>
                    <p class="small">{{ $report->suggestion->prevention_tips }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
