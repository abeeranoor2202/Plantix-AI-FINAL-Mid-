@extends('layouts.app')

@section('title', 'Crop Recommendation #' . $recommendation->id)

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Crop Recommendation <span class="text-muted">#{{ $recommendation->id }}</span></h2>
        <a href="{{ route('admin.ai.crop-recommendations') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- User Info --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">User Details</div>
                <div class="card-body">
                    <p class="mb-1"><strong>Name:</strong> {{ $recommendation->user->name ?? '—' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $recommendation->user->email ?? '—' }}</p>
                    <p class="mb-0"><strong>Date:</strong> {{ $recommendation->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Input Params --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">Input Parameters</div>
                <div class="card-body">
                    @php $params = $recommendation->input_params ?? []; @endphp
                    <table class="table table-sm table-borderless mb-0">
                        @foreach($params as $key => $value)
                        <tr>
                            <td class="text-capitalize text-muted">{{ str_replace('_', ' ', $key) }}</td>
                            <td><strong>{{ $value }}</strong></td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        {{-- Explanation --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">AI Explanation</div>
                <div class="card-body">
                    <p class="mb-0 small">{{ $recommendation->explanation ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recommended Crops Table --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header fw-semibold">Recommended Crops (Ranked)</div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Rank</th>
                        <th>Crop</th>
                        <th>Confidence</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recommendation->recommended_crops ?? [] as $i => $crop)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            @if($i === 0)
                                <span class="badge bg-success">{{ $crop['crop'] ?? '—' }}</span>
                            @else
                                {{ $crop['crop'] ?? '—' }}
                            @endif
                        </td>
                        <td>
                            <div class="progress" style="height:6px; width:80px; display:inline-flex;">
                                <div class="progress-bar bg-success" style="width:{{ $crop['confidence'] ?? 0 }}%"></div>
                            </div>
                            <span class="ms-1 small">{{ $crop['confidence'] ?? 0 }}%</span>
                        </td>
                        <td class="small text-muted">{{ $crop['notes'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-3">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
