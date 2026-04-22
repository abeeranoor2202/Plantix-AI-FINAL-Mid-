@extends('layouts.dashboard')

@section('title', 'Book Appointment | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')@endsection

@section('content')
<div class="py-5" style="background: var(--agri-bg); min-height: calc(100vh - 80px);">
    <div class="container-agri pb-5 mb-5">
        <div class="row pt-4 justify-content-center">
            <div class="col-lg-8">
                
                <div class="mb-4 d-flex align-items-center gap-3">
                    <a href="{{ route('appointments') }}" class="btn-agri btn-agri-outline d-flex align-items-center p-2 rounded-circle border-0" style="width: 40px; height: 40px; justify-content: center; background: white; box-shadow: var(--agri-shadow-sm);">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="fw-bold mb-0 text-dark">
                        Book a Consultation
                    </h2>
                </div>

                <!-- Main Content -->
                <div class="card-agri p-0 border-0 overflow-hidden">
                    <div class="p-4 border-bottom" style="background: var(--agri-primary-light);">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 50px; height: 50px; background: white; color: var(--agri-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: var(--agri-shadow-sm);">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Schedule an Expert</h4>
                                <p class="text-muted mb-0 small">Get connected with top agricultural specialists.</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 p-md-5">
                        @if($errors->any())
                            <div class="alert alert-danger mb-4" style="border-radius: var(--agri-radius-sm);">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $selectedExpertId = (int) old('expert_id', $experts->first()?->id ?? 0);
                            $selectedType = old('type', 'online');
                        @endphp

                        <form method="POST" action="{{ route('appointment.store') }}" id="appointmentBookingForm" novalidate>
                            @csrf
                            <div class="row g-4">
                                <!-- Expert Selection -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Select Expert</label>
                                    <div class="position-relative">
                                        <select name="expert_id" id="expertSelect" class="form-agri pe-5" style="appearance: none; cursor: pointer; background-color: white;">
                                            <option value="">Any available expert</option>
                                            @foreach($experts as $expert)
                                            <option value="{{ $expert->id }}"
                                                    data-location="{{ trim(($expert->profile?->address ? $expert->profile->address . ', ' : '') . ($expert->profile?->city ? $expert->profile->city . ', ' : '') . ($expert->profile?->country ?? '')) }}"
                                                    data-map-link="{{ $expert->profile?->map_link }}"
                                                    {{ $selectedExpertId === $expert->id ? 'selected' : '' }}>
                                                {{ $expert->user->name ?? 'Expert #'.$expert->id }}
                                                @if($expert->specialization) &mdash; {{ $expert->specialization }}@endif
                                            </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down position-absolute text-muted" style="right: 15px; top: 35%; pointer-events: none;"></i>
                                    </div>
                                    @error('expert_id')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <!-- Appointment Type -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Appointment Type <span class="text-danger">*</span></label>
                                    <select name="type" id="appointmentType" class="form-agri" style="cursor: pointer; background-color: white;">
                                        <option value="physical" {{ $selectedType === 'physical' ? 'selected' : '' }}>Physical</option>
                                        <option value="online" {{ $selectedType === 'online' ? 'selected' : '' }}>Online</option>
                                    </select>
                                    @error('type')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <!-- Date & Time -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Select Date <span class="text-danger">*</span></label>
                                    <input type="date" id="slotDate" class="form-agri"
                                           min="{{ now()->toDateString() }}"
                                           value="{{ old('slot_date', now()->toDateString()) }}"
                                           required style="cursor: pointer; background-color: white;">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Available Time Slot <span class="text-danger">*</span></label>
                                    <select name="slot_id" id="slotSelect" class="form-agri" required style="cursor: pointer; background-color: white;">
                                        <option value="">Select a date first</option>
                                    </select>
                                    <div id="slotUnavailableState" class="text-muted mt-1 small" style="display: none;">This expert has not set availability yet</div>
                                    <div id="slotFeedback" class="text-muted mt-1 small" style="display: none;"></div>
                                    <div id="slotInlineError" class="text-danger mt-1 small" style="display: none;"></div>
                                    @error('slot_id')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12" id="bookingSummaryBlock" style="display: none;">
                                    <div class="alert alert-light border mb-0" style="border-radius: var(--agri-radius-sm);">
                                        <span id="bookingSummaryText" class="text-dark small fw-semibold"></span>
                                    </div>
                                </div>

                                <!-- Physical Location -->
                                <div class="col-12 {{ $selectedType === 'physical' ? '' : 'd-none' }}" id="physicalBlock">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Location</label>
                                    <input type="text" id="locationPreview" class="form-agri bg-light" value="" readonly>
                                    <input type="hidden" name="location" id="locationInput" value="{{ old('location') }}">
                                    <small class="text-muted d-block mt-2">This will be auto-filled from the expert profile.</small>
                                </div>

                                <!-- Online Note -->
                                <div class="col-12 {{ $selectedType === 'online' ? '' : 'd-none' }}" id="onlineBlock">
                                    <div class="alert alert-light border mb-0" style="border-radius: var(--agri-radius-sm);">
                                        Meeting link will be provided after confirmation.
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Concern / Notes (Optional)</label>
                                    <textarea name="notes" rows="4" class="form-agri" placeholder="Describe your concern or any details you'd like the expert to know beforehand...   ">{{ old('notes') }}</textarea>
                                    @error('notes')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex align-items-center justify-content-end gap-3">
                                <a href="{{ route('appointments') }}" class="btn-agri text-dark bg-light border-0" style="padding: 10px 24px; text-decoration: none;">Cancel</a>
                                <button type="submit" id="confirmBookingBtn" class="btn-agri btn-agri-primary" style="padding: 10px 24px;" disabled>Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bookingForm = document.getElementById('appointmentBookingForm');
    const expertSelect = document.getElementById('expertSelect');
    const typeSelect = document.getElementById('appointmentType');
    const slotDate = document.getElementById('slotDate');
    const slotSelect = document.getElementById('slotSelect');
    const slotFeedback = document.getElementById('slotFeedback');
    const slotInlineError = document.getElementById('slotInlineError');
    const slotUnavailableState = document.getElementById('slotUnavailableState');
    const summaryBlock = document.getElementById('bookingSummaryBlock');
    const summaryText = document.getElementById('bookingSummaryText');
    const confirmBookingBtn = document.getElementById('confirmBookingBtn');
    const physicalBlock = document.getElementById('physicalBlock');
    const onlineBlock = document.getElementById('onlineBlock');
    const locationPreview = document.getElementById('locationPreview');
    const locationInput = document.getElementById('locationInput');
    const oldSlotId = "{{ old('slot_id') }}";
    let activeSlotsRequest = null;
    let activeDate = '';
    let hasAvailableSlots = false;
    let currentSlotsRequestId = 0;

    function updateLocation() {
        const selected = expertSelect?.selectedOptions?.[0];
        const location = selected?.dataset?.location || '';
        if (locationPreview) locationPreview.value = location || 'Expert location will appear here.';
        if (locationInput) locationInput.value = location;
    }

    function updateTypeVisibility() {
        const isPhysical = typeSelect?.value === 'physical';
        physicalBlock?.classList.toggle('d-none', !isPhysical);
        onlineBlock?.classList.toggle('d-none', isPhysical);
        if (!isPhysical && locationInput) locationInput.value = '';
        if (isPhysical) updateLocation();
    }

    function formatDisplayDate(dateString) {
        if (!dateString) return '';
        const dateObj = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(dateObj.getTime())) return dateString;
        return dateObj.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function to12hTime(time24) {
        if (!time24) return '';
        const parts = time24.split(':');
        const hours = Number(parts[0]);
        const minutes = parts[1] || '00';
        const suffix = hours >= 12 ? 'PM' : 'AM';
        const normalized = hours % 12 || 12;
        return `${normalized}:${minutes} ${suffix}`;
    }

    function setSlotFeedback(message, tone) {
        if (!slotFeedback) return;
        slotFeedback.textContent = message || '';
        slotFeedback.style.display = message ? 'block' : 'none';
        slotFeedback.classList.remove('text-muted', 'text-danger', 'text-success');
        if (tone === 'error') {
            slotFeedback.classList.add('text-danger');
        } else if (tone === 'success') {
            slotFeedback.classList.add('text-success');
        } else {
            slotFeedback.classList.add('text-muted');
        }
    }

    function clearInlineError() {
        if (!slotInlineError) return;
        slotInlineError.textContent = '';
        slotInlineError.style.display = 'none';
    }

    function showInlineError(message) {
        if (!slotInlineError) return;
        slotInlineError.textContent = message;
        slotInlineError.style.display = 'block';
    }

    function updateSummary() {
        if (!summaryBlock || !summaryText || !slotSelect || !slotDate) return;
        const selectedOption = slotSelect.selectedOptions?.[0];
        if (!selectedOption || !selectedOption.value) {
            summaryBlock.style.display = 'none';
            summaryText.textContent = '';
            return;
        }

        const label = selectedOption.textContent || '';
        const start = label.split(' - ')[0] || '';
        const readableDate = formatDisplayDate(slotDate.value);
        summaryText.textContent = `You are booking: ${readableDate}, ${to12hTime(start)}`;
        summaryBlock.style.display = 'block';
    }

    function updateSubmitState() {
        if (!confirmBookingBtn || !slotDate || !slotSelect) return;
        const isValid = Boolean(slotDate.value) && Boolean(slotSelect.value) && hasAvailableSlots;
        confirmBookingBtn.disabled = !isValid;
    }

    function showSlotDropdown() {
        if (!slotSelect) return;
        slotSelect.style.display = '';
        if (slotUnavailableState) slotUnavailableState.style.display = 'none';
    }

    function showSlotUnavailableState() {
        if (!slotSelect) return;
        slotSelect.style.display = 'none';
        if (slotUnavailableState) slotUnavailableState.style.display = 'block';
    }

    function animateSlotDropdownReveal() {
        if (!slotSelect) return;
        slotSelect.style.transition = 'opacity 180ms ease';
        slotSelect.style.opacity = '0.6';
        requestAnimationFrame(function () {
            slotSelect.style.opacity = '1';
        });
    }

    function applyNoSlotsState(date, nextDate, hasAvailabilityTemplate) {
        hasAvailableSlots = false;

        if (hasAvailabilityTemplate === false) {
            showSlotUnavailableState();
            setSlotFeedback('This expert has not set availability yet.', 'default');
            updateSummary();
            updateSubmitState();
            return;
        }

        showSlotDropdown();
        if (slotSelect) {
            slotSelect.innerHTML = '<option value="">No slots available on this date</option>';
            slotSelect.disabled = true;
        }

        let message = 'No slots available on this date. Try selecting another date.';
        if (nextDate) {
            message += ` Next available date: ${formatDisplayDate(nextDate)}.`;
        }
        setSlotFeedback(message, 'default');
        updateSummary();
        updateSubmitState();
    }

    async function findNextAvailableDate(expertId, date, requestId, signal) {
        const baseDate = new Date(date + 'T00:00:00');
        if (Number.isNaN(baseDate.getTime())) return null;

        for (let i = 1; i <= 14; i++) {
            if (signal?.aborted || requestId !== currentSlotsRequestId) {
                return null;
            }
            const probe = new Date(baseDate);
            probe.setDate(baseDate.getDate() + i);
            const probeDate = probe.toISOString().slice(0, 10);
            try {
                const url = `{{ route('appointment.slots') }}?expert_id=${encodeURIComponent(expertId)}&date=${encodeURIComponent(probeDate)}`;
                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal,
                });
                if (!response.ok) continue;
                const payload = await response.json();
                const slots = Array.isArray(payload.slots) ? payload.slots : [];
                if (slots.length > 0) {
                    return probeDate;
                }
            } catch (e) {
                if (e && e.name === 'AbortError') {
                    return null;
                }
                return null;
            }
        }

        return null;
    }

    function renderSlots(slots) {
        if (!slotSelect) return;
        showSlotDropdown();
        slotSelect.innerHTML = '';

        if (!Array.isArray(slots) || slots.length === 0) {
            hasAvailableSlots = false;
            slotSelect.innerHTML = '<option value="">No slots available on this date</option>';
            slotSelect.disabled = true;
            updateSummary();
            updateSubmitState();
            return;
        }

        hasAvailableSlots = true;
        slotSelect.disabled = false;

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select an available slot';
        slotSelect.appendChild(placeholder);

        slots.forEach(function (slot) {
            const option = document.createElement('option');
            option.value = slot.id;
            option.textContent = slot.start_time + ' - ' + slot.end_time;
            if (String(oldSlotId) === String(slot.id)) {
                option.selected = true;
            }
            slotSelect.appendChild(option);
        });

        if (!slotSelect.value && slots[0]?.id) {
            slotSelect.value = String(slots[0].id);
        }

        animateSlotDropdownReveal();
        setSlotFeedback(`Slots loaded for ${formatDisplayDate(activeDate)}.`, 'success');
        updateSummary();
        updateSubmitState();
    }

    async function loadSlots() {
        if (!expertSelect || !slotDate || !slotSelect) return;
        const requestId = ++currentSlotsRequestId;
        const expertId = expertSelect.value;
        const date = slotDate.value;
        activeDate = date;
        clearInlineError();

        if (!expertId || !date) {
            hasAvailableSlots = false;
            showSlotDropdown();
            slotSelect.innerHTML = '<option value="">Select expert and date</option>';
            slotSelect.disabled = true;
            setSlotFeedback('Select an expert and date to check availability.', 'default');
            updateSummary();
            updateSubmitState();
            return;
        }

        if (activeSlotsRequest) {
            activeSlotsRequest.abort();
        }

        activeSlotsRequest = new AbortController();
        showSlotDropdown();
        slotSelect.disabled = true;
        hasAvailableSlots = false;
        setSlotFeedback('Checking availability...', 'default');

        slotSelect.innerHTML = '<option value="">Checking availability...</option>';

        try {
            const url = `{{ route('appointment.slots') }}?expert_id=${encodeURIComponent(expertId)}&date=${encodeURIComponent(date)}`;
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeSlotsRequest.signal,
            });
            const payload = await response.json();
            if (requestId !== currentSlotsRequestId || slotDate.value !== date || expertSelect.value !== expertId) {
                return;
            }
            const slots = payload.slots || [];

            if (Array.isArray(slots) && slots.length > 0) {
                renderSlots(slots);
                return;
            }

            const nextAvailableDate = await findNextAvailableDate(expertId, date, requestId, activeSlotsRequest?.signal);
            if (requestId !== currentSlotsRequestId || slotDate.value !== date || expertSelect.value !== expertId) {
                return;
            }
            applyNoSlotsState(date, nextAvailableDate, payload.has_availability_template);

            if (nextAvailableDate && slotDate.value === date) {
                slotDate.value = nextAvailableDate;
                await loadSlots();
            }
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            hasAvailableSlots = false;
            showSlotDropdown();
            slotSelect.innerHTML = '<option value="">Unable to load slots</option>';
            slotSelect.disabled = true;
            setSlotFeedback('Unable to check availability right now. Please try another date.', 'error');
            updateSummary();
            updateSubmitState();
        } finally {
            activeSlotsRequest = null;
        }
    }

    expertSelect?.addEventListener('change', function () {
        updateLocation();
        loadSlots();
    });
    slotDate?.addEventListener('change', loadSlots);
    slotSelect?.addEventListener('change', function () {
        clearInlineError();
        updateSummary();
        updateSubmitState();
    });

    bookingForm?.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearInlineError();
        if (!slotSelect.value) {
            showInlineError('Please choose an available slot before booking.');
            updateSubmitState();
            return;
        }

        if (confirmBookingBtn) {
            confirmBookingBtn.disabled = true;
            confirmBookingBtn.textContent = 'Checking availability...';
        }

        try {
            const formData = new FormData(bookingForm);
            const response = await fetch(bookingForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData,
            });

            if (response.redirected) {
                window.location.href = response.url;
                return;
            }

            if (response.ok) {
                window.location.href = '{{ route('appointments') }}';
                return;
            }

            const payload = await response.json().catch(() => ({}));
            const firstError = payload?.errors
                ? Object.values(payload.errors).flat()[0]
                : (payload?.message || 'Selected slot is no longer available');
            showInlineError(String(firstError || 'Selected slot is no longer available'));
            await loadSlots();
        } catch (error) {
            showInlineError('Selected slot is no longer available');
            await loadSlots();
        } finally {
            if (confirmBookingBtn) {
                confirmBookingBtn.textContent = 'Confirm Booking';
            }
            updateSubmitState();
        }
    });

    typeSelect?.addEventListener('change', updateTypeVisibility);
    updateLocation();
    updateTypeVisibility();
    loadSlots();
});
</script>
@endsection
