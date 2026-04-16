@extends('expert.layouts.app')

@section('title', 'Edit Appointment #' . $appointment->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Edit Appointment #{{ $appointment->id }}</h4>
        <p class="text-muted mb-0">Update appointment details for this consultation.</p>
    </div>
    <div class="d-flex gap-2">
        <x-button :href="route('expert.appointments.show', $appointment)" variant="outline" icon="fas fa-arrow-left">Back</x-button>
    </div>
</div>

@if($errors->any())
    <x-card class="mb-4">
        <div class="p-3">
            <div class="alert alert-danger mb-0">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </x-card>
@endif

<x-card>
    <div class="p-4">
        <form method="POST" action="{{ route('expert.appointments.update', $appointment) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label text-muted small">Scheduled At</label>
                <input type="datetime-local"
                       name="scheduled_at"
                       class="form-agri"
                       value="{{ old('scheduled_at', optional($appointment->scheduled_at)->format('Y-m-d\\TH:i')) }}"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label text-muted small">Duration (minutes)</label>
                <input type="number"
                       name="duration_minutes"
                       class="form-agri"
                       min="15"
                       max="240"
                       step="5"
                       value="{{ old('duration_minutes', $appointment->duration_minutes ?? 60) }}"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label text-muted small">Topic</label>
                <input type="text"
                       name="topic"
                       class="form-agri"
                       maxlength="255"
                       value="{{ old('topic', $appointment->topic) }}"
                       placeholder="Consultation topic">
            </div>

            @if($appointment->isOnline())
                <div class="col-md-6">
                    <label class="form-label text-muted small">Meeting Link</label>
                    <input type="url"
                           name="meeting_link"
                           class="form-agri"
                           value="{{ old('meeting_link', $appointment->meeting_link) }}"
                           placeholder="https://..."
                           required>
                </div>
            @endif

            @if($appointment->isPhysical())
                <div class="col-md-6">
                    <label class="form-label text-muted small">Location</label>
                    <input type="text"
                           name="location"
                           class="form-agri"
                           maxlength="255"
                           value="{{ old('location', $appointment->location) }}"
                           placeholder="Session location"
                           required>
                </div>
            @endif

            <div class="col-12">
                <label class="form-label text-muted small">Notes</label>
                <textarea name="notes" class="form-agri" rows="4" maxlength="2000" placeholder="Optional notes">{{ old('notes', $appointment->notes) }}</textarea>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                <x-button :href="route('expert.appointments.show', $appointment)" variant="outline">Cancel</x-button>
                <x-button type="submit" variant="primary" icon="fas fa-save">Save Changes</x-button>
            </div>
        </form>
    </div>
</x-card>
@endsection
