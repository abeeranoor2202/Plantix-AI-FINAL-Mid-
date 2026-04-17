@extends('expert.layouts.app')

@section('title', 'Edit Appointment #' . $appointment->id)

@section('content')
<div style="margin-bottom: 32px;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
        <a href="{{ route('expert.appointments.index') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Appointments</a>
        <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
        <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Edit Appointment</span>
    </div>
    <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Edit Appointment #{{ $appointment->id }}</h1>
    <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Update appointment details for this consultation</p>
    <div style="margin-top: 12px; color: var(--agri-text-heading); font-size: 14px; font-weight: 600; display: grid; gap: 6px;">
        <div><i class="fas fa-user" style="width: 18px; color: var(--agri-text-muted);"></i> {{ $appointment->user->name ?? 'N/A' }}</div>
        <div><i class="fas fa-envelope" style="width: 18px; color: var(--agri-text-muted);"></i> {{ $appointment->user->email ?? 'N/A' }}</div>
        <div><i class="fas fa-clock" style="width: 18px; color: var(--agri-text-muted);"></i> {{ optional($appointment->scheduled_at)->format('d M Y • h:i A') }}</div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-agri" style="padding: 40px;">
            <form method="POST" action="{{ route('expert.appointments.update', $appointment) }}" id="appointment-edit-form" novalidate>
                @csrf
                @method('PUT')

                <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-alt"></i> Appointment Details
                    </h4>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Scheduled At <span class="text-danger">*</span></label>
                            <input
                                type="datetime-local"
                                id="scheduled_at"
                                name="scheduled_at"
                                class="form-agri @error('scheduled_at') is-invalid @enderror"
                                value="{{ old('scheduled_at', optional($appointment->scheduled_at)->format('Y-m-d\\TH:i')) }}"
                                required
                            >
                            @error('scheduled_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback" id="scheduled-at-feedback">Select a future date and time.</div>
                            @enderror
                            <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 8px; font-weight: 600;">Timezone: PKT</div>
                        </div>

                        <div class="col-md-6">
                            <label class="agri-label">Duration (minutes) <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                id="duration_minutes"
                                name="duration_minutes"
                                class="form-agri @error('duration_minutes') is-invalid @enderror"
                                min="15"
                                max="240"
                                step="5"
                                value="{{ old('duration_minutes', $appointment->duration_minutes ?? 60) }}"
                                required
                            >
                            @error('duration_minutes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback" id="duration-feedback">Duration must be greater than 0.</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-file-alt"></i> Consultation Info
                    </h4>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="agri-label">Topic</label>
                            <input
                                type="text"
                                name="topic"
                                class="form-agri @error('topic') is-invalid @enderror"
                                maxlength="255"
                                value="{{ old('topic', $appointment->topic) }}"
                                placeholder="Consultation topic"
                            >
                            @error('topic')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="agri-label">Customer Notes</label>
                            <textarea name="notes" class="form-agri @error('notes') is-invalid @enderror" rows="4" maxlength="2000" placeholder="Optional notes">{{ old('notes', $appointment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 40px; background: var(--agri-bg); padding: 24px; border-radius: 16px; border: 1px solid var(--agri-border);">
                    <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-video"></i> Meeting
                    </h4>

                    <div class="row g-4">
                        @if($appointment->isOnline())
                            <div class="col-12">
                                <label class="agri-label">Meeting Link <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                                    <input
                                        type="url"
                                        id="meeting_link"
                                        name="meeting_link"
                                        class="form-agri @error('meeting_link') is-invalid @enderror"
                                        value="{{ old('meeting_link', $appointment->meeting_link) }}"
                                        placeholder="https://..."
                                        required
                                    >
                                    <button type="button" id="copy-link-btn" class="btn btn-agri btn-agri-outline" title="Copy Link">
                                        <i class="fas fa-copy"></i> Copy Link
                                    </button>
                                    <a href="#" id="open-link-btn" class="btn btn-agri btn-agri-outline d-none" target="_blank" rel="noopener noreferrer" title="Open Link">
                                        <i class="fas fa-external-link-alt"></i> Open Link
                                    </a>
                                </div>
                                @error('meeting_link')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback" id="meeting-link-feedback">Enter a valid meeting URL</div>
                                @enderror
                            </div>
                        @endif

                        @if($appointment->isPhysical())
                            <div class="col-12">
                                <label class="agri-label">Location <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="location"
                                    class="form-agri @error('location') is-invalid @enderror"
                                    maxlength="255"
                                    value="{{ old('location', $appointment->location) }}"
                                    placeholder="Session location"
                                    required
                                >
                                @error('location')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </div>
                </div>

                <div style="display: flex; gap: 16px; border-top: 1px solid var(--agri-border); padding-top: 32px;">
                    <button type="submit" class="btn-agri btn-agri-primary" style="flex: 2; height: 50px; font-size: 16px;">
                        <i class="fas fa-save" style="margin-right: 8px;"></i> Save Changes
                    </button>
                    <a href="{{ route('expert.appointments.show', $appointment) }}" class="btn-agri btn-agri-outline" style="flex: 1; height: 50px; display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px;">
                        Back
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .agri-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--agri-text-heading);
        margin-bottom: 8px;
        display: block;
    }

    .form-agri.is-invalid,
    .form-agri.is-invalid:focus {
        border-color: var(--agri-error) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.14) !important;
    }

    .invalid-feedback {
        font-size: 12px;
        font-weight: 600;
        margin-top: 6px;
    }

    .input-group .form-agri {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        function parseLocalDateTime(value) {
            if (!value) {
                return null;
            }

            var parts = value.split(/[-T:]/);
            if (parts.length < 5) {
                return null;
            }

            return new Date(
                Number(parts[0]),
                Number(parts[1]) - 1,
                Number(parts[2]),
                Number(parts[3]),
                Number(parts[4]),
                0,
                0
            );
        }

        function formatMinDate(date) {
            var year = date.getFullYear();
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var day = String(date.getDate()).padStart(2, '0');
            var hour = String(date.getHours()).padStart(2, '0');
            var minute = String(date.getMinutes()).padStart(2, '0');
            return year + '-' + month + '-' + day + 'T' + hour + ':' + minute;
        }

        function validateMeetingField(meetingInput) {
            if (!meetingInput) {
                return true;
            }

            var feedback = document.getElementById('meeting-link-feedback');

            var value = meetingInput.value.trim();
            var valid = false;

            if (meetingInput.required) {
                valid = value !== '';
            } else {
                valid = true;
            }

            if (valid && value !== '') {
                try {
                    var parsed = new URL(value);
                    valid = parsed.protocol === 'http:' || parsed.protocol === 'https:';
                } catch (e) {
                    valid = false;
                }
            }

            meetingInput.classList.toggle('is-invalid', !valid);
            if (feedback) {
                feedback.classList.toggle('d-block', !valid);
            }
            return valid;
        }

        function syncMeetingActions(meetingInput, openBtn) {
            if (!meetingInput || !openBtn) {
                return;
            }

            var valid = validateMeetingField(meetingInput);
            var value = meetingInput.value.trim();
            if (valid && value !== '') {
                openBtn.href = value;
                openBtn.classList.remove('d-none');
            } else {
                openBtn.href = '#';
                openBtn.classList.add('d-none');
            }
        }

        function validateDateField(dateInput) {
            if (!dateInput) {
                return true;
            }

            var feedback = document.getElementById('scheduled-at-feedback');

            var selected = parseLocalDateTime(dateInput.value);
            var now = new Date();
            var valid = selected instanceof Date && !isNaN(selected) && selected.getTime() > now.getTime();
            dateInput.classList.toggle('is-invalid', !valid);
            if (feedback) {
                feedback.classList.toggle('d-block', !valid);
            }
            return valid;
        }

        function validateDurationField(durationInput) {
            if (!durationInput) {
                return true;
            }

            var feedback = document.getElementById('duration-feedback');

            var value = Number(durationInput.value);
            var valid = Number.isFinite(value) && value > 0 && value >= 15;
            durationInput.classList.toggle('is-invalid', !valid);
            if (feedback) {
                feedback.classList.toggle('d-block', !valid);
            }
            return valid;
        }

        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('appointment-edit-form');
            if (!form) {
                return;
            }

            var dateInput = document.getElementById('scheduled_at');
            var durationInput = document.getElementById('duration_minutes');
            var meetingInput = document.getElementById('meeting_link');
            var copyBtn = document.getElementById('copy-link-btn');
            var openBtn = document.getElementById('open-link-btn');

            if (dateInput) {
                dateInput.min = formatMinDate(new Date());
                dateInput.addEventListener('blur', function () {
                    validateDateField(dateInput);
                });
                dateInput.addEventListener('change', function () {
                    validateDateField(dateInput);
                });
            }

            if (durationInput) {
                durationInput.addEventListener('blur', function () {
                    validateDurationField(durationInput);
                });
                durationInput.addEventListener('change', function () {
                    validateDurationField(durationInput);
                });
            }

            if (meetingInput) {
                meetingInput.addEventListener('blur', function () {
                    validateMeetingField(meetingInput);
                    syncMeetingActions(meetingInput, openBtn);
                });
                meetingInput.addEventListener('input', function () {
                    syncMeetingActions(meetingInput, openBtn);
                });
                syncMeetingActions(meetingInput, openBtn);
            }

            if (copyBtn && meetingInput) {
                copyBtn.addEventListener('click', async function () {
                    var value = meetingInput.value.trim();
                    if (!value) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(value);
                    } catch (e) {
                        meetingInput.select();
                        document.execCommand('copy');
                        meetingInput.setSelectionRange(0, 0);
                    }
                });
            }

            form.addEventListener('submit', function (event) {
                var dateValid = validateDateField(dateInput);
                var durationValid = validateDurationField(durationInput);
                var meetingValid = validateMeetingField(meetingInput);
                syncMeetingActions(meetingInput, openBtn);

                if (!dateValid || !durationValid || !meetingValid) {
                    event.preventDefault();
                }
            });
        });
    })();
</script>
@endpush
