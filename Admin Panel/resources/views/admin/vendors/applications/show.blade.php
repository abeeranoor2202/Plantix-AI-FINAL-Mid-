@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; margin-bottom:32px;">
        <div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:8px;">
                <a href="{{ route('admin.vendor-applications.index') }}" style="text-decoration:none; color:var(--agri-text-muted); font-size:14px; font-weight:600;">Vendor Applications</a>
                <i class="fas fa-chevron-right" style="font-size:10px; color:var(--agri-text-muted);"></i>
                <span style="color:var(--agri-primary); font-size:14px; font-weight:600;">{{ $application->application_number }}</span>
            </div>
            <h1 style="font-size:28px; font-weight:700; color:var(--agri-primary-dark); margin:0;">Application Review</h1>
            <p style="color:var(--agri-text-muted); margin:4px 0 0 0;">Detailed onboarding record for {{ $application->business_name }}.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-agri" style="padding:24px; margin-bottom:24px;">
                <h4 style="font-size:18px; font-weight:700; color:var(--agri-primary-dark); margin-bottom:20px;">Business Details</h4>
                <div class="row g-3">
                    <div class="col-md-6"><strong>Business Name:</strong><div>{{ $application->business_name }}</div></div>
                    <div class="col-md-6"><strong>Owner Name:</strong><div>{{ $application->owner_name }}</div></div>
                    <div class="col-md-6"><strong>Email:</strong><div>{{ $application->email }}</div></div>
                    <div class="col-md-6"><strong>Phone:</strong><div>{{ $application->phone }}</div></div>
                    <div class="col-md-6"><strong>Category:</strong><div>{{ $application->business_category ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Region:</strong><div>{{ trim(($application->city ?: '') . ' ' . ($application->region ?: '')) ?: '—' }}</div></div>
                    <div class="col-12"><strong>Address:</strong><div>{{ $application->business_address ?: '—' }}</div></div>
                </div>
            </div>

            <div class="card-agri" style="padding:24px;">
                <h4 style="font-size:18px; font-weight:700; color:var(--agri-primary-dark); margin-bottom:20px;">Banking & Identity</h4>
                <div class="row g-3">
                    <div class="col-md-6"><strong>Tax ID / CNIC:</strong><div>{{ $application->cnic_tax_id ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Bank Name:</strong><div>{{ $application->bank_name ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Account Name:</strong><div>{{ $application->bank_account_name ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Account Number:</strong><div>{{ $application->bank_account_number ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>IBAN:</strong><div>{{ $application->iban ?: '—' }}</div></div>
                    <div class="col-md-6"><strong>Status:</strong><div>{{ ucfirst(str_replace('_', ' ', $application->status)) }}</div></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-agri" style="padding:24px; margin-bottom:24px;">
                <h4 style="font-size:18px; font-weight:700; color:var(--agri-primary-dark); margin-bottom:16px;">Actions</h4>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <form method="POST" action="{{ route('admin.vendor-applications.under-review', $application) }}">
                        @csrf
                        <button type="submit" class="btn-agri" style="width:100%; justify-content:center; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">
                            <i class="fas fa-search"></i> Mark Under Review
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.vendor-applications.approve', $application) }}">
                        @csrf
                        <button type="submit" class="btn-agri btn-agri-primary" style="width:100%; justify-content:center;">
                            <i class="fas fa-check"></i> Approve Application
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.vendor-applications.reject', $application) }}">
                        @csrf
                        <input type="hidden" name="reason" value="Rejected from admin review">
                        <button type="submit" class="btn-agri" style="width:100%; justify-content:center; background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">
                            <i class="fas fa-times"></i> Reject Application
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.vendor-applications.suspend', $application) }}">
                        @csrf
                        <input type="hidden" name="reason" value="Suspended from admin review">
                        <button type="submit" class="btn-agri" style="width:100%; justify-content:center; background:#fef3c7; color:#92400e; border:1px solid #fde68a;">
                            <i class="fas fa-ban"></i> Suspend Application
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-agri" style="padding:24px;">
                <h4 style="font-size:18px; font-weight:700; color:var(--agri-primary-dark); margin-bottom:16px;">Timeline</h4>
                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:10px; color:var(--agri-text-heading);">
                    <li><strong>Submitted:</strong> {{ optional($application->submitted_at)->format('d M Y H:i') ?? '—' }}</li>
                    <li><strong>Reviewed:</strong> {{ optional($application->reviewed_at)->format('d M Y H:i') ?? '—' }}</li>
                    <li><strong>Approved:</strong> {{ optional($application->approved_at)->format('d M Y H:i') ?? '—' }}</li>
                    <li><strong>Rejected:</strong> {{ optional($application->rejected_at)->format('d M Y H:i') ?? '—' }}</li>
                    <li><strong>Suspended:</strong> {{ optional($application->suspended_at)->format('d M Y H:i') ?? '—' }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
