@extends('layouts.app')

@section('title', 'AI Agriculture — Dashboard')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-robot me-2 text-success"></i>AI Agriculture Modules</h2>
        <span class="badge bg-success fs-6">Admin Portal</span>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['crop_recommendations'] }}</div>
                            <div class="small opacity-75">Crop Recs</div>
                        </div>
                        <i class="fas fa-seedling fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['crop_plans'] }}</div>
                            <div class="small opacity-75">Crop Plans</div>
                        </div>
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['disease_reports'] }}</div>
                            <div class="small opacity-75">Disease Reports</div>
                        </div>
                        <i class="fas fa-virus fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['pending_disease_reports'] }}</div>
                            <div class="small opacity-75">Pending Review</div>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-danger h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['fertilizer_recommendations'] }}</div>
                            <div class="small opacity-75">Fertilizer Recs</div>
                        </div>
                        <i class="fas fa-flask fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm text-white bg-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-4 fw-bold">{{ $stats['seasonal_data'] }}</div>
                            <div class="small opacity-75">Seasonal Records</div>
                        </div>
                        <i class="fas fa-cloud-sun fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Nav --}}
    <div class="row g-3">
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.crop-recommendations') }}" class="card card-body text-decoration-none text-dark border shadow-sm h-100 hover-lift">
                <i class="fas fa-seedling fa-2x text-primary mb-2"></i>
                <h6 class="mb-1">Crop Recommendations</h6>
                <p class="text-muted small mb-0">View all AI-generated crop recommendations by users.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.crop-plans') }}" class="card card-body text-decoration-none text-dark border shadow-sm h-100 hover-lift">
                <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                <h6 class="mb-1">Crop Plans</h6>
                <p class="text-muted small mb-0">Review seasonal crop plans created by farmers.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.disease-reports') }}" class="card card-body text-decoration-none text-dark border shadow-sm h-100 hover-lift">
                <i class="fas fa-virus fa-2x text-warning mb-2"></i>
                <h6 class="mb-1">Disease Reports</h6>
                <p class="text-muted small mb-0">Review disease detection reports and assign verified diagnoses.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.fertilizer') }}" class="card card-body text-decoration-none text-dark border shadow-sm h-100 hover-lift">
                <i class="fas fa-flask fa-2x text-danger mb-2"></i>
                <h6 class="mb-1">Fertilizer Recommendations</h6>
                <p class="text-muted small mb-0">Overview of NPK-based fertilizer plans generated.</p>
            </a>
        </div>
        <div class="col-md-4 col-lg-3">
            <a href="{{ route('admin.ai.seasonal-data') }}" class="card card-body text-decoration-none text-dark border shadow-sm h-100 hover-lift">
                <i class="fas fa-database fa-2x text-secondary mb-2"></i>
                <h6 class="mb-1">Seasonal Data</h6>
                <p class="text-muted small mb-0">Manage crop calendar reference data (Rabi/Kharif/Zaid).</p>
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
