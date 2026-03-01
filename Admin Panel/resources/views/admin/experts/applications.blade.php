@extends('layouts.app')

@section('title', 'Expert Applications')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Expert Applications</h4>
        <a href="{{ route('admin.experts.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Back to Experts
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
                <div class="card border-0 shadow-sm h-100">
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
    <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
        <select name="status" class="form-select form-select-sm w-auto">
            <option value="">Needs Review (default)</option>
            <option value="pending"      @selected(request('status') === 'pending')>Pending</option>
            <option value="under_review" @selected(request('status') === 'under_review')>Under Review</option>
            <option value="approved"     @selected(request('status') === 'approved')>Approved</option>
            <option value="rejected"     @selected(request('status') === 'rejected')>Rejected</option>
        </select>
        <button class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.experts.applications.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
    </form>

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
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
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
                                        <button class="btn btn-outline-info btn-sm">Start Review</button>
                                    </form>
                                    @endif

                                    {{-- Approve --}}
                                    @if(in_array($app->status, ['pending', 'under_review']))
                                    <form action="{{ route('admin.experts.applications.approve', $app->id) }}" method="POST"
                                          onsubmit="return confirm('Approve this application and create an expert account?')">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Approve</button>
                                    </form>

                                    {{-- Reject --}}
                                    <button class="btn btn-outline-danger btn-sm"
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
                                                    <textarea name="reason" class="form-control" rows="4" required
                                                              placeholder="Explain why this application is being rejected..."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Confirm Rejection</button>
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
