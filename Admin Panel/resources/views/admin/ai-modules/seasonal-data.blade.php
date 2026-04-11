@extends('layouts.app')

@section('title', 'Seasonal Data')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Seasonal Data</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Seasonal Crop Data</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Manage sowing and harvest baselines in a consistent data registry.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.ai.dashboard') }}" class="btn-agri btn-agri-outline" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none; font-weight: 700;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> AI Dashboard
            </a>
            <button type="button" class="btn-agri btn-agri-primary" style="height: 42px; border: none; font-weight: 700;" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus" style="margin-right: 8px;"></i> Add Entry
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="card-agri mb-4" style="background: #ecfdf5; border: 1px solid #86efac; border-radius: 12px; padding: 12px 20px; color: #166534; font-weight: 700;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Seasonal Entry List</h4>
            <span class="badge rounded-pill bg-secondary">{{ $seasonalData->count() }} Entries</span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">#</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Crop</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Season</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Sowing</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Harvest</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Water Needs</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Yield (kg/acre)</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($seasonalData as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->id }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">{{ $item->crop_name }}</td>
                            <td class="px-4 py-3"><span class="badge rounded-pill bg-primary">{{ strtoupper($item->season) }}</span></td>
                            <td class="px-4 py-3">{{ $item->sowing_month }}</td>
                            <td class="px-4 py-3">{{ $item->harvest_month }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $item->water_needs ?? '')) }}</td>
                            <td class="px-4 py-3">{{ number_format($item->avg_yield_kg_acre ?? 0) }}</td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px; border: none;" onclick="editRow({{ $item->id }}, @json($item))" title="Edit"><i class="fas fa-pen"></i></button>
                                    <form method="POST" action="{{ route('admin.ai.seasonal-data.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #ef4444; border-radius: 999px; border: none;" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5" style="color: var(--agri-text-muted);">No seasonal data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 18px 40px rgba(0,0,0,0.12);">
            <form method="POST" id="seasonalForm" action="{{ route('admin.ai.seasonal-data.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header" style="border: none; padding: 20px 22px 0;">
                    <h5 class="modal-title" id="modalTitle" style="font-weight: 700; color: var(--agri-text-heading); font-size: 17px;">Add Seasonal Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 20px 22px;">
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="crop_name" class="form-agri" placeholder="Crop name" required></div>
                        <div class="col-md-6">
                            <select name="season" class="form-agri" required>
                                <option value="Rabi">Rabi</option>
                                <option value="Kharif">Kharif</option>
                                <option value="Zaid">Zaid</option>
                            </select>
                        </div>
                        <div class="col-md-6"><input type="text" name="sowing_month" class="form-agri" placeholder="Sowing month" required></div>
                        <div class="col-md-6"><input type="text" name="harvest_month" class="form-agri" placeholder="Harvest month" required></div>
                        <div class="col-md-6">
                            <select name="water_needs" class="form-agri">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="very_high">Very High</option>
                            </select>
                        </div>
                        <div class="col-md-6"><input type="number" min="0" name="avg_yield_kg_acre" class="form-agri" placeholder="Yield (kg/acre)"></div>
                        <div class="col-12"><textarea name="notes" class="form-agri" rows="3" placeholder="Notes (optional)"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer" style="border: none; padding: 0 22px 22px; display: flex; gap: 10px;">
                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal" style="flex: 1; height: 42px;">Cancel</button>
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 42px;">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editRow(id, data) {
    const form = document.getElementById('seasonalForm');
    form.action = `/admin/ai-modules/seasonal-data/${id}`;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('modalTitle').textContent = 'Edit Seasonal Data';
    ['crop_name','season','sowing_month','harvest_month','water_needs','avg_yield_kg_acre','notes'].forEach(function (f) {
        const el = form.querySelector(`[name="${f}"]`);
        if (el) el.value = data[f] ?? '';
    });
    new bootstrap.Modal(document.getElementById('addModal')).show();
}
</script>
@endpush
