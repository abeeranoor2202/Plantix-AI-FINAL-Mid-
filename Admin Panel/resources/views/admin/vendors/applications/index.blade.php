@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:32px;">
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <a href="{{ route('admin.dashboard') }}" style="text-decoration:none; color:var(--agri-text-muted); font-size:14px; font-weight:600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size:10px; color:var(--agri-text-muted);"></i>
                <span style="color:var(--agri-primary); font-size:14px; font-weight:600;">Vendor Applications</span>
            </div>
            <h1 style="font-size:28px; font-weight:700; color:var(--agri-primary-dark); margin:0;">Vendor Applications</h1>
            <p style="color:var(--agri-text-muted); margin:4px 0 0 0;">Review, approve, reject, or suspend vendor onboarding requests.</p>
        </div>
    </div>

    <div class="card-agri" style="padding:0; overflow:hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align:middle;">
                <thead style="background:var(--agri-bg);">
                    <tr>
                        <th style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Application</th>
                        <th style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Business</th>
                        <th style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Owner</th>
                        <th style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Status</th>
                        <th style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Submitted</th>
                        <th class="text-end" style="padding:16px 24px; font-size:12px; font-weight:600; color:var(--agri-text-muted); text-transform:uppercase; border:none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td class="px-4 py-3">
                            <div style="font-weight:700; color:var(--agri-text-heading);">{{ $application->application_number }}</div>
                            <div style="font-size:12px; color:var(--agri-text-muted);">{{ $application->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div style="font-weight:700; color:var(--agri-text-heading);">{{ $application->business_name }}</div>
                            <div style="font-size:12px; color:var(--agri-text-muted);">{{ $application->business_category ?: 'General' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $application->owner_name }}</td>
                        <td class="px-4 py-3">
                            <span class="badge rounded-pill {{ $application->status === 'approved' ? 'bg-success' : ($application->status === 'rejected' ? 'bg-danger' : ($application->status === 'suspended' ? 'bg-warning' : 'bg-secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ optional($application->submitted_at)->format('d M Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="text-end" style="display:flex; justify-content:flex-end; gap:8px;">
                                <a href="{{ route('admin.vendor-applications.show', $application) }}" class="btn-agri" style="padding:8px; background:var(--agri-bg); color:#2563eb; border-radius:999px;" title="View"><i class="fas fa-eye"></i></a>
                                <form method="POST" action="{{ route('admin.vendor-applications.approve', $application) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn-agri" style="padding:8px; background:#ecfdf5; color:#047857; border-radius:999px; border:none;" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5" style="color:var(--agri-text-muted);">No vendor applications found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="px-4 py-3 bg-white border-top">{{ $applications->links() }}</div>
        @endif
    </div>
</div>
@endsection
