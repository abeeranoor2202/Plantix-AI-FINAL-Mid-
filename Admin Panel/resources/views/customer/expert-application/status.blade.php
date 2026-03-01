@extends('layouts.frontend')

@section('title', 'Application Status | Plantix-AI')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">

            {{-- Header --}}
            <div class="mb-4">
                <h2 class="fw-bold">Expert Application Status</h2>
                <p class="text-muted">Track the progress of your application to become a Plantix AI expert.</p>
            </div>

            {{-- Status Banner --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    @php
                        $iconMap = [
                            'pending'      => ['icon' => 'bi-hourglass-split',   'color' => 'text-warning'],
                            'under_review' => ['icon' => 'bi-search',            'color' => 'text-info'],
                            'approved'     => ['icon' => 'bi-check-circle-fill', 'color' => 'text-success'],
                            'rejected'     => ['icon' => 'bi-x-circle-fill',     'color' => 'text-danger'],
                        ];
                        $icon = $iconMap[$application->status] ?? ['icon' => 'bi-question-circle', 'color' => 'text-secondary'];
                    @endphp
                    <i class="bi {{ $icon['icon'] }} {{ $icon['color'] }} fs-1"></i>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fs-5 fw-semibold">{{ $application->status_label }}</span>
                            {!! $application->status_badge !!}
                        </div>
                        <div class="text-muted small">
                            Submitted {{ $application->created_at->diffForHumans() }}
                            &nbsp;·&nbsp;
                            {{ $application->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Rejection reason --}}
            @if($application->isRejected() && $application->admin_notes)
            <div class="alert alert-danger mb-4">
                <strong>Rejection Reason:</strong><br>
                {{ $application->admin_notes }}
            </div>
            @endif

            {{-- Approval message --}}
            @if($application->isApproved())
            <div class="alert alert-success mb-4">
                <strong>Congratulations!</strong> Your application has been approved.
                Please <a href="{{ route('login') }}">log into your expert account</a> to get started.
            </div>
            @endif

            {{-- Progress Timeline --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Application Progress</div>
                <div class="card-body">
                    @php
                        $steps = [
                            ['key' => 'submitted',     'label' => 'Application Submitted',    'date' => $application->created_at],
                            ['key' => 'under_review',  'label' => 'Under Review',             'date' => null],
                            ['key' => 'decision',      'label' => 'Decision Made',            'date' => $application->reviewed_at],
                        ];

                        // Determine which step we're at
                        $currentStep = match($application->status) {
                            'pending'      => 0,
                            'under_review' => 1,
                            default        => 2,   // approved or rejected
                        };
                    @endphp

                    <ol class="list-unstyled mb-0">
                        @foreach($steps as $i => $step)
                        @php
                            $done    = $i < $currentStep;
                            $active  = $i === $currentStep;
                            $pending = $i > $currentStep;
                        @endphp
                        <li class="d-flex align-items-start gap-3 mb-3">
                            {{-- Circle --}}
                            <div class="flex-shrink-0 mt-1">
                                @if($done)
                                    <span class="badge rounded-circle bg-success p-2"><i class="bi bi-check"></i></span>
                                @elseif($active && !$application->isRejected())
                                    <span class="badge rounded-circle bg-primary p-2"><i class="bi bi-arrow-right"></i></span>
                                @elseif($active && $application->isRejected())
                                    <span class="badge rounded-circle bg-danger p-2"><i class="bi bi-x"></i></span>
                                @else
                                    <span class="badge rounded-circle bg-secondary p-2"><i class="bi bi-circle"></i></span>
                                @endif
                            </div>
                            {{-- Text --}}
                            <div>
                                <div class="fw-semibold {{ $pending ? 'text-muted' : '' }}">{{ $step['label'] }}</div>
                                @if($step['date'])
                                <div class="text-muted small">{{ $step['date']->format('M d, Y \a\t g:i A') }}</div>
                                @elseif($active && !in_array($application->status, ['approved','rejected']))
                                <div class="text-muted small">In progress…</div>
                                @elseif($pending)
                                <div class="text-muted small">Pending</div>
                                @endif
                            </div>
                        </li>
                        @if(!$loop->last)
                        <li class="ms-2 mb-2" style="border-left:2px solid #dee2e6; height:16px;"></li>
                        @endif
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Application details summary --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Your Application Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Full Name</dt>
                        <dd class="col-sm-8">{{ $application->full_name }}</dd>

                        <dt class="col-sm-4">Specialization</dt>
                        <dd class="col-sm-8">{{ $application->specialization }}</dd>

                        <dt class="col-sm-4">Experience</dt>
                        <dd class="col-sm-8">{{ $application->experience_years }} year(s)</dd>

                        @if($application->qualifications)
                        <dt class="col-sm-4">Qualifications</dt>
                        <dd class="col-sm-8">{{ $application->qualifications }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary">
                    &larr; Back to Dashboard
                </a>

                @if($application->isRejected())
                <a href="{{ route('customer.expert-application.create') }}" class="btn btn-primary">
                    Apply Again
                </a>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
