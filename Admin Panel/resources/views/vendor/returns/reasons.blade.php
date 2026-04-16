@extends('vendor.layouts.app')
@section('title', 'Return Reasons')
@section('page-title', 'Return Reasons')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill text-success fs-4 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex align-items-start">
            <i class="bi bi-exclamation-triangle-fill text-danger fs-4 me-3 mt-1"></i>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li class="small">{{ $e }}</li>@endforeach</ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $vendorId = auth('vendor')->user()->vendor->id;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-tags-fill me-2 text-primary"></i>Return Reasons</h4>
        <span class="text-muted small fw-medium mt-1 d-block">Configure the reasons customers can select when requesting a return</span>
    </div>
    <a href="{{ route('vendor.returns.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="bi bi-arrow-return-left me-2"></i>Back to Returns
    </a>
</div>

<div class="row g-4">
    {{-- Add New Reason --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm" style="border-radius:16px; position:sticky; top:90px;">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle-fill me-2 text-success fs-5"></i>Add New Reason</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('vendor.return-reasons.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase mb-2">Reason Text <span class="text-danger">*</span></label>
                        <input type="text" name="reason" value="{{ old('reason') }}" required maxlength="255"
                               class="form-control bg-light border-0 rounded-3 @error('reason') is-invalid @enderror"
                               placeholder="e.g. Damaged on arrival">
                        @error('reason')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active_new" value="1" checked>
                        <label class="form-check-label small fw-medium text-muted" for="is_active_new">Active (visible to customers)</label>
                    </div>
                    <button type="submit" class="btn btn-success rounded-pill fw-bold px-4 py-2 w-100 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Add Reason
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Reasons List --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-check me-2 text-primary fs-5"></i>All Reasons
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill ms-2 px-2 py-1" style="font-size:11px;">{{ $reasons->count() }} total</span>
                </h6>
                <span class="text-muted small">
                    <span class="text-success fw-bold">{{ $reasons->where('is_active', true)->count() }}</span> active &nbsp;·&nbsp;
                    <span class="text-secondary fw-bold">{{ $reasons->where('is_active', false)->count() }}</span> inactive
                </span>
            </div>
            <div class="card-body p-0">
                @if($reasons->isEmpty())
                    <div class="text-center text-muted py-5 my-2">
                        <i class="bi bi-tags fs-1 d-block mb-3 opacity-25"></i>
                        <h6 class="fw-bold">No return reasons yet</h6>
                        <p class="small mb-0">Add your first reason using the form on the left.</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($reasons as $reason)
                        @php
                            $isOwned = (int) ($reason->vendor_id ?? 0) === (int) $vendorId;
                        @endphp
                        <li class="list-group-item px-4 py-3 d-flex align-items-center gap-3 {{ !$reason->is_active ? 'bg-light' : '' }}">
                            {{-- Status indicator --}}
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $reason->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-15"
                                  style="width:10px; height:10px; min-width:10px;">
                                <span class="rounded-circle {{ $reason->is_active ? 'bg-success' : 'bg-secondary' }}" style="width:6px; height:6px; display:block;"></span>
                            </span>

                            {{-- Inline edit form --}}
                            @if($isOwned)
                                <form method="POST" action="{{ route('vendor.return-reasons.update', $reason->id) }}"
                                      class="d-flex align-items-center gap-2 flex-grow-1" id="edit-form-{{ $reason->id }}">
                                    @csrf @method('PATCH')
                                    <input type="text" name="reason" value="{{ $reason->reason }}" required maxlength="255"
                                           class="form-control form-control-sm bg-{{ $reason->is_active ? 'white' : 'light' }} border rounded-3 fw-medium text-dark shadow-sm"
                                           style="font-size:14px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap shadow-sm">
                                        <i class="bi bi-save me-1"></i>Save
                                    </button>
                                </form>
                            @else
                                <div class="d-flex align-items-center gap-2 flex-grow-1">
                                    <input type="text" value="{{ $reason->reason }}" readonly
                                           class="form-control form-control-sm bg-light border rounded-3 fw-medium text-dark shadow-sm"
                                           style="font-size:14px;">
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1">Global</span>
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="d-flex align-items-center gap-2 ms-auto text-nowrap">
                                {{-- Toggle active --}}
                                @if($isOwned)
                                    <form method="POST" action="{{ route('vendor.return-reasons.toggle', $reason->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $reason->is_active ? 'Deactivate' : 'Activate' }}"
                                                class="btn btn-sm {{ $reason->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                style="width:32px; height:32px;">
                                            <i class="bi {{ $reason->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }} fs-6"></i>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                @if($isOwned)
                                    <form method="POST" action="{{ route('vendor.return-reasons.destroy', $reason->id) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Delete"
                                                class="btn btn-sm btn-outline-danger rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                                style="width:32px; height:32px;"
                                                onclick="return confirm('Delete this reason? If it has been used in returns, it will be deactivated instead.')">
                                            <i class="bi bi-trash fs-6"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            {{-- Usage count badge --}}
                            @php $usageCount = $reason->returns()->count(); @endphp
                            @if($usageCount > 0)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 text-nowrap" style="font-size:10px;">
                                    {{ $usageCount }} use{{ $usageCount !== 1 ? 's' : '' }}
                                </span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Info card --}}
        <div class="alert alert-info bg-info bg-opacity-10 border-0 rounded-3 mt-4 d-flex align-items-start shadow-sm">
            <i class="bi bi-info-circle-fill text-info fs-5 me-3 mt-1"></i>
            <div class="small">
                <strong class="d-block mb-1">How Return Reasons Work</strong>
                Customers select from active reasons when submitting a return request. Inactive reasons are hidden from the form but preserved in historical data. Reasons with existing returns cannot be deleted and are <em>deactivated</em> instead.
            </div>
        </div>
    </div>
</div>
@endsection
