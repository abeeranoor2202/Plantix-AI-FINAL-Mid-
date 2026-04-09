@extends('layouts.app')

@section('title', 'Expert Applications')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 16px; flex-wrap: wrap;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.experts.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Experts</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Applications</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Expert Applications</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review incoming expert applications and manage approval decisions.</p>
        </div>
        <a href="{{ route('admin.experts.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Experts
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Pending',      'key' => 'pending',      'color' => 'warning'],
            ['label' => 'Under Review', 'key' => 'under_review', 'color' => 'info'],
            ['label' => 'Approved',     'key' => 'approved',     'color' => 'success'],
            ['label' => 'Rejected',     'key' => 'rejected',     'color' => 'danger'],
        ] as $s)
        <div class="col-6 col-md-3">
            <a href="{{ request()->fullUrlWithQuery(['status' => $s['key']]) }}" class="text-decoration-none">
                <div class="card-agri h-100">
                    <div class="card-body text-center">
                        <div class="fs-2 fw-bold text-{{ $s['color'] }}">{{ $stats[$s['key']] ?? 0 }}</div>
                        <div class="text-muted small">{{ $s['label'] }}</div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <div class="card-agri mb-3" style="padding: 16px;">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="status" class="form-agri" style="width: auto; min-width: 220px;">
                <option value="">Needs Review (default)</option>
                <option value="pending"      @selected(request('status') === 'pending')>Pending</option>
                <option value="under_review" @selected(request('status') === 'under_review')>Under Review</option>
                <option value="approved"     @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected"     @selected(request('status') === 'rejected')>Rejected</option>
            </select>
            <button class="btn-agri btn-agri-primary" type="submit">Filter</button>
            <a href="{{ route('admin.experts.applications.index') }}" class="btn-agri btn-agri-outline" style="text-decoration: none;">Reset</a>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Table --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Applicant</th>
                            <th>Specialization</th>
                            <th>Account Type</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                        <tr>
                            <td class="text-muted small">{{ $app->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $app->full_name }}</div>
                                <div class="text-muted small">{{ $app->user?->email }}</div>
                            </td>
                            <td>{{ $app->specialization }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ ucfirst($app->account_type ?? 'individual') }}
                                </span>
                            </td>
                            <td>{{ $app->experience_years }} yr{{ $app->experience_years != 1 ? 's' : '' }}</td>
                            <td>
                                <span class="badge bg-{{ $app->status_badge }}">
                                    {{ $app->status_label }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $app->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    {{-- Under Review --}}
                                    @if($app->isPending())
                                    <form action="{{ route('admin.experts.applications.under-review', $app->id) }}" method="POST">
                                        @csrf
                                        <button class="btn-agri" style="padding: 6px 10px; background: #e0f2fe; color: #0c4a6e; border: 1px solid #bae6fd; font-size: 12px;">Start Review</button>
                                    </form>
                                    @endif

                                    {{-- Approve --}}
                                    @if(in_array($app->status, ['pending', 'under_review']))
                                    <form action="{{ route('admin.experts.applications.approve', $app->id) }}" method="POST"
                                          onsubmit="return confirm('Approve this application and create an expert account?')">
                                        @csrf
                                        <button class="btn-agri btn-agri-primary" style="padding: 6px 10px; font-size: 12px;">Approve</button>
                                    </form>

                                    {{-- Reject --}}
                                    <button class="btn-agri" style="padding: 6px 10px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; font-size: 12px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $app->id }}">
                                        Reject
                                    </button>
                                    @endif
                                </div>

                                {{-- Reject modal --}}
                                @if(in_array($app->status, ['pending', 'under_review']))
                                <div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.experts.applications.reject', $app->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Application #{{ $app->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-agri" rows="4" required
                                                              placeholder="Explain why this application is being rejected..."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn-agri" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">Confirm Rejection</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No applications found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($applications->hasPages())
        <div class="card-footer bg-transparent">
            {{ $applications->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
