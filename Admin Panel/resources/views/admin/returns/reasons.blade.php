@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <a href="{{ route('admin.returns.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Returns & Refunds</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Configuration</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Return Reasons</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Define the standard list of options customers can select when requesting a return.</p>
    </div>

    @if(session('success'))
        <div style="background: var(--agri-success-light); color: var(--agri-success); padding: 16px; border-radius: 12px; margin-bottom: 24px; font-weight: 600; display: flex; align-items: center; justify-content: space-between;">
            <span><i class="fas fa-check-circle me-2"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="background:none; border:none; color:inherit; cursor:pointer;"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Add Reason Form --}}
        <div class="col-lg-4">
            <div class="card-agri" style="padding: 24px; background: white; sticky-top: 24px;">
                <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-primary-dark); margin-bottom: 20px;">
                    <i class="fas fa-plus-circle me-2"></i> Register New Reason
                </h5>
                <form method="POST" action="{{ route('admin.returns.reasons.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="agri-label">REASON STATEMENT</label>
                        <input type="text" name="reason"
                               class="form-agri @error('reason') is-invalid @enderror"
                               placeholder="e.g. Item damaged during transit"
                               value="{{ old('reason') }}" required style="height: 44px;">
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="agri-label">DESCRIPTION (OPTIONAL)</label>
                        <textarea name="description" class="form-agri @error('description') is-invalid @enderror"
                                  rows="3" placeholder="Additional context shown to customers...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn-agri btn-agri-primary" style="width: 100%; height: 48px; font-weight: 700;">
                        Add to Registry
                    </button>
                </form>
            </div>
        </div>

        {{-- Existing Reasons --}}
        <div class="col-lg-8">
            <div class="card-agri" style="padding: 0; overflow: hidden; background: white;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white; display: flex; justify-content: space-between; align-items: center;">
                    <h5 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Active Reason Registry</h5>
                    <span style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); background: var(--agri-bg); padding: 4px 12px; border-radius: 100px;">{{ $reasons->count() }} Entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="vertical-align: middle;">
                        <thead style="background: var(--agri-bg);">
                            <tr>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">#ID</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Established Reason Statement</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Status</th>
                                <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reasons as $reason)
                            <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                                <td style="padding: 16px 24px; color: var(--agri-text-muted); font-size: 12px; font-weight: 700;">#{{ $reason->id }}</td>
                                <td style="padding: 16px 24px;">
                                    <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $reason->reason }}</div>
                                    @if($reason->description)
                                        <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 2px;">{{ \Illuminate\Support\Str::limit($reason->description, 80) }}</div>
                                    @endif
                                    <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500; margin-top: 2px;">Created on {{ $reason->created_at->format('M d, Y') }}</div>
                                </td>
                                <td style="padding: 16px 24px;" class="text-center">
                                    @if($reason->is_active)
                                        <span style="background: var(--agri-success-light); color: var(--agri-success); padding: 4px 12px; border-radius: 100px; font-size: 10px; font-weight: 800; text-transform: uppercase;">Active</span>
                                    @else
                                        <span style="background: var(--agri-bg); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 10px; font-weight: 800; text-transform: uppercase;">Inactive</span>
                                    @endif
                                </td>
                                <td style="padding: 16px 24px;" class="text-end">
                                    <form method="POST" action="{{ route('admin.returns.reasons.destroy', $reason->id) }}" onsubmit="return confirm('Archive this return reason?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; color: var(--agri-error); background: #FEF2F2; border: none; border-radius: 8px;" title="Remove Reason">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div style="color: var(--agri-border); font-size: 48px; margin-bottom: 20px;"><i class="fas fa-list-ul"></i></div>
                                    <div style="font-weight: 700; color: var(--agri-text-muted);">No return reasons established yet.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .agri-label { font-size: 11px; font-weight: 700; color: var(--agri-text-muted); margin-bottom: 10px; display: block; text-transform: uppercase; letter-spacing: 0.5px; }
</style>
@endsection
