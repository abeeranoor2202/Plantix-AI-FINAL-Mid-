@extends('expert.layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Appointments</h4>
        <p class="text-muted mb-0">Manage consultation requests and session lifecycle.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <x-card>
            <div class="p-3">
                <div class="small text-muted">Total</div>
                <div class="h4 mb-0 fw-bold text-dark">{{ $stats['total'] }}</div>
            </div>
        </x-card>
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-card>
            <div class="p-3">
                <div class="small text-muted">Pending</div>
                <div class="h4 mb-0 fw-bold text-dark">{{ $stats['pending'] }}</div>
            </div>
        </x-card>
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-card>
            <div class="p-3">
                <div class="small text-muted">Upcoming</div>
                <div class="h4 mb-0 fw-bold text-dark">{{ $stats['upcoming'] }}</div>
            </div>
        </x-card>
    </div>
    <div class="col-sm-6 col-lg-3">
        <x-card>
            <div class="p-3">
                <div class="small text-muted">Completed</div>
                <div class="h4 mb-0 fw-bold text-dark">{{ $stats['completed'] }}</div>
            </div>
        </x-card>
    </div>
</div>

<x-card class="mb-4">
    <div class="p-3 p-lg-4">
        <form method="GET" action="{{ route('expert.appointments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small">Search</label>
                <x-input name="search" :value="$filters['search'] ?? ''" placeholder="Farmer or topic" />
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small">Status</label>
                <select name="status" class="form-agri">
                    <option value="">All</option>
                    @foreach(['pending_payment','pending_expert_approval','confirmed','reschedule_requested','rescheduled','completed','rejected','cancelled'] as $st)
                        <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ', $st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small">Date From</label>
                <x-input type="date" name="date_from" :value="$filters['date_from'] ?? ''" />
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small">Date To</label>
                <x-input type="date" name="date_to" :value="$filters['date_to'] ?? ''" />
            </div>
            <div class="col-md-2 d-flex gap-2">
                <x-button type="submit" variant="primary" icon="fas fa-filter" class="w-100">Apply</x-button>
                <x-button :href="route('expert.appointments.index')" variant="outline" class="w-100">Reset</x-button>
            </div>
        </form>
    </div>
</x-card>

<x-card>
    <x-table>
        <thead class="bg-light">
            <tr>
                <th class="px-4 py-3 small text-muted">ID</th>
                <th class="px-4 py-3 small text-muted">FARMER</th>
                <th class="px-4 py-3 small text-muted">TOPIC</th>
                <th class="px-4 py-3 small text-muted">TYPE</th>
                <th class="px-4 py-3 small text-muted">SCHEDULED</th>
                <th class="px-4 py-3 small text-muted">STATUS</th>
                <th class="px-4 py-3 small text-muted">FEE</th>
                <th class="px-4 py-3 small text-muted text-end">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($appointments->items() as $appt)
                @php
                    $statusVariant = match($appt->status) {
                        'completed', 'confirmed' => 'success',
                        'pending_payment', 'pending_expert_approval', 'reschedule_requested', 'rescheduled' => 'warning',
                        'rejected', 'cancelled' => 'danger',
                        default => 'secondary',
                    };

                    $canEdit = !in_array($appt->status, ['completed', 'cancelled', 'rejected'], true);
                    $canDelete = in_array($appt->status, ['pending_expert_approval', 'pending'], true);
                @endphp
                <tr>
                    <td class="px-4 py-3">#{{ $appt->id }}</td>
                    <td class="px-4 py-3">
                        <div class="fw-bold text-dark">{{ $appt->user->name }}</div>
                        <small class="text-muted">{{ $appt->user->email }}</small>
                    </td>
                    <td class="px-4 py-3">{{ Str::limit($appt->topic ?? 'General consultation', 35) }}</td>
                    <td class="px-4 py-3">
                        <x-badge :variant="$appt->type === 'physical' ? 'success' : 'info'">{{ strtoupper($appt->type_label) }}</x-badge>
                    </td>
                    <td class="px-4 py-3">{{ $appt->scheduled_at?->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-3">
                        <x-badge :variant="$statusVariant">{{ ucfirst(str_replace('_',' ', $appt->status)) }}</x-badge>
                    </td>
                    <td class="px-4 py-3">PKR {{ number_format($appt->fee) }}</td>
                    <td class="px-4 py-3 text-end">
                        <div class="d-inline-flex align-items-center gap-2">
                            <a href="{{ route('expert.appointments.show', $appt) }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="View">
                                <i class="fas fa-eye text-primary"></i>
                            </a>

                            @if($canEdit)
                                <a href="{{ route('expert.appointments.edit', $appt) }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Edit">
                                    <i class="fas fa-pen text-success"></i>
                                </a>
                            @else
                                <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Edit unavailable" disabled>
                                    <i class="fas fa-pen text-muted"></i>
                                </button>
                            @endif

                            @if($canDelete)
                                <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteAppointmentModal{{ $appt->id }}">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center" title="Delete unavailable" disabled>
                                    <i class="fas fa-trash text-muted"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                @if($canDelete)
                    <div class="modal fade" id="deleteAppointmentModal{{ $appt->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form method="POST" action="{{ route('expert.appointments.delete', $appt) }}" class="modal-content">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Appointment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-0 text-muted">Are you sure you want to delete appointment #{{ $appt->id }}? This action cannot be undone.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn-agri btn-agri-outline" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="mdi mdi-calendar-blank-outline d-block fs-2 mb-2"></i>
                        No appointments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</x-card>

@if($appointments->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $appointments->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
