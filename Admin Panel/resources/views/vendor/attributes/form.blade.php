@extends('vendor.layouts.app')
@section('title', $attribute ? 'Edit Attribute' : 'New Attribute')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            {{-- Breadcrumb --}}
            <div style="margin-bottom: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <a href="{{ route('vendor.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                    <a href="{{ route('vendor.attributes.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Attributes</a>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                    <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">{{ $attribute ? 'Edit' : 'Create' }}</span>
                </div>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">
                    {{ $attribute ? 'Edit Attribute' : 'Create New Attribute' }}
                </h1>
                <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">
                    {{ $attribute ? 'Update your attribute details and options.' : 'Add a new attribute to enrich your product listings.' }}
                </p>
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4" style="border-radius:12px;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius:12px;">
                    <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="card-agri">
                <div class="card-body p-4 p-md-5">

                    @if($attribute)
                        <form action="{{ route('vendor.attributes.update', $attribute->id) }}" method="POST">
                            @csrf @method('PUT')
                    @else
                        <form action="{{ route('vendor.attributes.store') }}" method="POST">
                            @csrf
                    @endif

                        {{-- Name --}}
                        <div class="mb-4">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">
                                Attribute Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3">
                                    <i class="fas fa-sliders-h text-muted"></i>
                                </span>
                                <input type="text" name="name"
                                       class="form-control form-control-lg fs-6 bg-light border-0 rounded-end-3 @error('name') is-invalid @enderror"
                                       value="{{ old('name', $attribute?->name ?: $attribute?->title) }}"
                                       placeholder="e.g. Color, Weight, Material" required>
                            </div>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Type --}}
                        <div class="mb-4">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">
                                Attribute Type <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="attrType"
                                    class="form-agri @error('type') is-invalid @enderror"
                                    style="height:52px;font-size:15px;font-weight:600;">
                                @foreach(['text' => 'Text (free input)', 'number' => 'Number', 'select' => 'Select (single choice)', 'multi-select' => 'Multi-Select (multiple choices)'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('type', $attribute?->type ?? 'text') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text mt-2 text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="typeHint">Choose how customers will enter this attribute on products.</span>
                            </div>
                        </div>

                        {{-- Unit (shown for text/number) --}}
                        <div class="mb-4" id="unitWrap">
                            <label class="form-label text-muted text-uppercase fw-bold small mb-2">Unit <span class="text-muted fw-normal">(optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3">
                                    <i class="fas fa-ruler text-muted"></i>
                                </span>
                                <input type="text" name="unit"
                                       class="form-control bg-light border-0 rounded-end-3 @error('unit') is-invalid @enderror"
                                       value="{{ old('unit', $attribute?->unit) }}"
                                       placeholder="e.g. kg, cm, L, pcs" maxlength="40">
                            </div>
                            @error('unit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Options (shown for select / multi-select) --}}
                        <div class="mb-5" id="valuesWrap" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label text-muted text-uppercase fw-bold small mb-0">
                                    Options <span class="text-danger">*</span>
                                </label>
                                <button type="button" id="addValueBtn"
                                        class="btn-agri btn-agri-outline py-1 px-3 d-flex align-items-center gap-2"
                                        style="font-size:13px;">
                                    <i class="fas fa-plus"></i> Add Option
                                </button>
                            </div>

                            <div id="valuesContainer">
                                @php
                                    $existingValues = old('values', $attribute?->values?->pluck('value')->toArray() ?? []);
                                @endphp
                                @forelse($existingValues as $i => $val)
                                    <div class="value-row d-flex align-items-center gap-2 mb-2">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-0 rounded-start-3 text-muted" style="font-size:12px;font-weight:700;">{{ $i + 1 }}</span>
                                            <input type="text" name="values[]"
                                                   class="form-control bg-light border-0 rounded-end-3"
                                                   value="{{ $val }}" placeholder="Option value" required>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-value rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width:36px;height:36px;" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @empty
                                    <div id="emptyValuesNote" class="text-center text-muted p-4 bg-light rounded border border-dashed">
                                        <i class="fas fa-list fs-3 mb-2 opacity-50 d-block"></i>
                                        <p class="small mb-0 fw-medium">No options yet. Click "Add Option" to add choices.</p>
                                    </div>
                                @endforelse
                            </div>

                            @error('values')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                            @error('values.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                            <a href="{{ route('vendor.attributes.index') }}"
                               class="btn-agri btn-agri-outline px-4 py-2 text-decoration-none d-flex align-items-center gap-2">
                                <i class="fas fa-times text-muted"></i> Cancel
                            </a>
                            <button type="submit" class="btn-agri btn-agri-primary px-5 py-2 d-flex align-items-center gap-2">
                                <i class="fas fa-save"></i>
                                {{ $attribute ? 'Update Attribute' : 'Create Attribute' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const typeSelect   = document.getElementById('attrType');
    const valuesWrap   = document.getElementById('valuesWrap');
    const unitWrap     = document.getElementById('unitWrap');
    const container    = document.getElementById('valuesContainer');
    const addBtn       = document.getElementById('addValueBtn');
    const typeHint     = document.getElementById('typeHint');
    let   rowCount     = container ? container.querySelectorAll('.value-row').length : 0;

    const hints = {
        'text':         'Customers type any text value (e.g. "Red", "XL").',
        'number':       'Customers enter a numeric value (e.g. 2.5, 100).',
        'select':       'Customers pick one option from a dropdown list.',
        'multi-select': 'Customers can pick multiple options from a list.',
    };

    function updateUI() {
        const type = typeSelect.value;
        const isChoice = type === 'select' || type === 'multi-select';
        valuesWrap.style.display = isChoice ? '' : 'none';
        unitWrap.style.display   = isChoice ? 'none' : '';
        typeHint.textContent     = hints[type] || '';
    }

    typeSelect.addEventListener('change', updateUI);
    updateUI(); // run on page load

    function makeRow(index, value) {
        const div = document.createElement('div');
        div.className = 'value-row d-flex align-items-center gap-2 mb-2';
        div.innerHTML = `
            <div class="input-group">
                <span class="input-group-text bg-light border-0 rounded-start-3 text-muted" style="font-size:12px;font-weight:700;">${index + 1}</span>
                <input type="text" name="values[]"
                       class="form-control bg-light border-0 rounded-end-3"
                       value="${value || ''}" placeholder="Option value" required>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-value rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:36px;height:36px;" title="Remove">
                <i class="fas fa-times"></i>
            </button>`;
        div.querySelector('.remove-value').addEventListener('click', () => {
            div.remove();
            reindex();
        });
        return div;
    }

    function reindex() {
        container.querySelectorAll('.value-row').forEach((row, i) => {
            const badge = row.querySelector('.input-group-text');
            if (badge) badge.textContent = i + 1;
        });
    }

    // Wire existing remove buttons
    container.querySelectorAll('.remove-value').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.value-row').remove();
            reindex();
        });
    });

    addBtn.addEventListener('click', () => {
        const empty = document.getElementById('emptyValuesNote');
        if (empty) empty.remove();
        const row = makeRow(rowCount++);
        container.appendChild(row);
        row.querySelector('input').focus();
    });
})();
</script>
@endpush
