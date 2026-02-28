@extends('layouts.frontend')

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

                        <form method="POST" action="{{ route('appointment.store') }}">
                            @csrf
                            <div class="row g-4">
                                <!-- Expert Selection -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Select Expert</label>
                                    <div class="position-relative">
                                        <select name="expert_id" class="form-agri pe-5" style="appearance: none; cursor: pointer; background-color: white;">
                                            <option value="">Any available expert</option>
                                            @foreach($experts as $expert)
                                            <option value="{{ $expert->id }}" {{ old('expert_id') == $expert->id ? 'selected' : '' }}>
                                                {{ $expert->user->name ?? 'Expert #'.$expert->id }}
                                                @if($expert->specialization) &mdash; {{ $expert->specialization }}@endif
                                            </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down position-absolute text-muted" style="right: 15px; top: 35%; pointer-events: none;"></i>
                                    </div>
                                    @error('expert_id')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
                                </div>

                                <!-- Date & Time -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-dark" style="font-size: 14px;">Date &amp; Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="scheduled_at" class="form-agri"
                                           value="{{ old('scheduled_at') }}"
                                           min="{{ now()->addHour()->format('Y-m-d\TH:i') }}" required style="cursor: pointer; background-color: white;">
                                    @error('scheduled_at')<div class="text-danger mt-1 small">{{ $message }}</div>@enderror
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
                                <button type="submit" class="btn-agri btn-agri-primary" style="padding: 10px 24px;">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
