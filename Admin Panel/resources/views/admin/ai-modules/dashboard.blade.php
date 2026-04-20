@extends('layouts.app')

@section('title', 'AI Agriculture — Dashboard')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboards</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">AI Agriculture Modules</span>
            </div>
            <h2 class="h4 mb-0" style="font-weight: 700; color: var(--agri-primary-dark);"><i class="fas fa-robot me-2 text-success"></i>AI Agriculture Modules</h2>
        </div>
        <span class="badge bg-success fs-6" style="border-radius: 12px; padding: 10px 16px;">Admin Portal</span>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: var(--agri-primary-dark);">{{ $stats['crop_recommendations'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Crop Recs</div>
                    </div>
                    <div style="background: var(--agri-primary-light); color: var(--agri-primary); width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-seedling fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: var(--agri-secondary);">{{ $stats['crop_plans'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Crop Plans</div>
                    </div>
                    <div style="background: #FEF3C7; color: #D97706; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-calendar-alt fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: #059669;">{{ $stats['disease_reports'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Disease Reports</div>
                    </div>
                    <div style="background: #D1FAE5; color: #047857; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-virus fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: #2563EB;">{{ $stats['pending_disease_reports'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Pending Review</div>
                    </div>
                    <div style="background: #DBEAFE; color: #1D4ED8; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: #DC2626;">{{ $stats['fertilizer_recommendations'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Fertilizer Recs</div>
                    </div>
                    <div style="background: #FEE2E2; color: #B91C1C; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-flask fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fs-4 fw-bold" style="color: #6B7280;">{{ $stats['seasonal_data'] }}</div>
                        <div class="small fw-bold" style="color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Seasonal Records</div>
                    </div>
                    <div style="background: #F3F4F6; color: #4B5563; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cloud-sun fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-agri mb-4" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0" style="font-weight: 700; color: var(--agri-primary-dark);">Crop Prediction API Monitoring</h4>
            @if(($apiMonitoring['available'] ?? false) === true)
                <span class="badge bg-success">Connected</span>
            @else
                <span class="badge bg-danger">Unavailable</span>
            @endif
        </div>

        @if(!empty($apiMonitoring['error']))
            <div class="alert alert-warning mb-0">{{ $apiMonitoring['error'] }}</div>
        @else
            @php
                $health = $apiMonitoring['health'] ?? [];
                $apiStats = $apiMonitoring['stats']['stats'] ?? [];
                $apiLogs = $apiMonitoring['logs'] ?? [];
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase fw-bold">Model Loaded</div>
                        <div class="fw-bold mt-1">{{ ($health['model_loaded'] ?? false) ? 'Yes' : 'No' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase fw-bold">Database Ready</div>
                        <div class="fw-bold mt-1">{{ ($health['database_ready'] ?? false) ? 'Yes' : 'No' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase fw-bold">Total Predictions</div>
                        <div class="fw-bold mt-1">{{ $apiStats['total_predictions'] ?? 0 }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border rounded-3 p-3 h-100 bg-light">
                        <div class="small text-muted text-uppercase fw-bold">Avg Confidence</div>
                        <div class="fw-bold mt-1">{{ isset($apiStats['avg_confidence']) ? number_format((float) $apiStats['avg_confidence'] * 100, 2).'%' : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Prediction</th>
                            <th>Confidence</th>
                            <th>Request ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($apiLogs as $log)
                            <tr>
                                <td>{{ $log['created_at'] ?? 'N/A' }}</td>
                                <td>{{ $log['prediction'] ?? 'N/A' }}</td>
                                <td>{{ isset($log['confidence']) ? number_format((float) $log['confidence'] * 100, 2).'%' : 'N/A' }}</td>
                                <td><small>{{ $log['request_id'] ?? 'N/A' }}</small></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No API prediction logs available yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Quick Nav --}}
    <div class="row g-4 mt-2">
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.crop-recommendations') }}" class="card-agri text-decoration-none h-100 hover-lift d-block" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="background: var(--agri-primary-light); color: var(--agri-primary); width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas fa-seedling fa-lg"></i>
                </div>
                <h6 style="color: var(--agri-text-heading); font-weight: 800; font-size: 15px;">Crop Recommendations</h6>
                <p style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600; margin: 0; line-height: 1.5;">View all AI-generated crop recommendations by users.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.crop-plans') }}" class="card-agri text-decoration-none h-100 hover-lift d-block" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="background: #FEF3C7; color: #D97706; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas fa-calendar-check fa-lg"></i>
                </div>
                <h6 style="color: var(--agri-text-heading); font-weight: 800; font-size: 15px;">Crop Plans</h6>
                <p style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600; margin: 0; line-height: 1.5;">Review seasonal crop plans created by farmers.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.disease-reports') }}" class="card-agri text-decoration-none h-100 hover-lift d-block" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="background: #D1FAE5; color: #059669; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas fa-virus fa-lg"></i>
                </div>
                <h6 style="color: var(--agri-text-heading); font-weight: 800; font-size: 15px;">Disease Reports</h6>
                <p style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600; margin: 0; line-height: 1.5;">Review disease detection reports and assign verified diagnoses.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.fertilizer') }}" class="card-agri text-decoration-none h-100 hover-lift d-block" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="background: #FEE2E2; color: #DC2626; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas fa-flask fa-lg"></i>
                </div>
                <h6 style="color: var(--agri-text-heading); font-weight: 800; font-size: 15px;">Fertilizer Recommendations</h6>
                <p style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600; margin: 0; line-height: 1.5;">Overview of NPK-based fertilizer plans generated.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.seasonal-data') }}" class="card-agri text-decoration-none h-100 hover-lift d-block" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="background: #F3F4F6; color: #6B7280; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <i class="fas fa-database fa-lg"></i>
                </div>
                <h6 style="color: var(--agri-text-heading); font-weight: 800; font-size: 15px;">Seasonal Data</h6>
                <p style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600; margin: 0; line-height: 1.5;">Manage crop calendar reference data (Rabi/Kharif/Zaid).</p>
            </a>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
.hover-lift { transition: transform .15s, box-shadow .15s; }
.hover-lift:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.12) !important; }
</style>
@endpush
