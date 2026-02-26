@extends('layouts.app')

@section('title', 'Seasonal Data')

@push('styles')
<style>
#modalForm .form-label { font-size: .85rem; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0"><i class="fas fa-database me-2 text-secondary"></i>Seasonal Crop Data</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.ai.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> AI Dashboard
            </a>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus me-1"></i> Add Entry
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Crop</th>
                        <th>Season</th>
                        <th>Sowing</th>
                        <th>Harvest</th>
                        <th>Water Needs</th>
                        <th>Yield (kg/acre)</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($seasonalData as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->crop_name }}</td>
                        <td><span class="badge bg-{{ $item->season === 'Rabi' ? 'primary' : ($item->season === 'Kharif' ? 'warning text-dark' : 'success') }}">
                            {{ $item->season }}
                        </span></td>
                        <td>{{ $item->sowing_month }}</td>
                        <td>{{ $item->harvest_month }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $item->water_needs ?? '')) }}</td>
                        <td>{{ number_format($item->avg_yield_kg_acre ?? 0) }}</td>
                        <td class="text-end">
                            <button class="btn btn-xs btn-outline-warning"
                                    onclick="editRow({{ $item->id }}, @json($item))">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.ai.seasonal-data.destroy', $item->id) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete this record?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No seasonal data. <a href="#" data-bs-toggle="modal" data-bs-target="#addModal">Add one.</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalForm">
            <form method="POST" id="seasonalForm" action="{{ route('admin.ai.seasonal-data.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Seasonal Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Crop Name</label>
                            <input type="text" name="crop_name" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Season</label>
                            <select name="season" class="form-select form-select-sm" required>
                                <option value="Rabi">Rabi</option>
                                <option value="Kharif">Kharif</option>
                                <option value="Zaid">Zaid</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sowing Month</label>
                            <input type="text" name="sowing_month" class="form-control form-control-sm" placeholder="e.g. October" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harvest Month</label>
                            <input type="text" name="harvest_month" class="form-control form-control-sm" placeholder="e.g. April" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Water Needs</label>
                            <select name="water_needs" class="form-select form-select-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="very_high">Very High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Avg Yield (kg/acre)</label>
                            <input type="number" name="avg_yield_kg_acre" class="form-control form-control-sm" min="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
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

    ['crop_name','season','sowing_month','harvest_month','water_needs','avg_yield_kg_acre','notes'].forEach(f => {
        const el = form.querySelector(`[name="${f}"]`);
        if (el) el.value = data[f] ?? '';
    });
    new bootstrap.Modal(document.getElementById('addModal')).show();
}
</script>
@endpush
