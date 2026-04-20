@extends('layouts.app')

@section('title', 'Crop Recommendation #' . $recommendation->id)

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.crop-recommendations') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Crop Recommendations</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Recommendation #{{ $recommendation->id }}</span>
            </div>
            <h2 class="h4 mb-0" style="font-weight: 700; color: var(--agri-primary-dark);">Crop Recommendation <span class="text-muted">#{{ $recommendation->id }}</span></h2>
        </div>
        <a href="{{ route('admin.ai.crop-recommendations') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700; padding: 10px 20px;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-4">
        {{-- User Info --}}
        <div class="col-md-4">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--agri-border); padding-bottom: 12px;">
                    <div style="width: 32px; height: 32px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">User Details</h5>
                </div>
                <div>
                    <p class="mb-2" style="font-size: 13px;"><strong style="color: var(--agri-text-muted); font-weight: 800; text-transform: uppercase; font-size: 11px;">Name:</strong> <br> <span style="font-weight: 600; color: var(--agri-text-heading);">{{ $recommendation->user->name ?? '—' }}</span></p>
                    <p class="mb-2" style="font-size: 13px;"><strong style="color: var(--agri-text-muted); font-weight: 800; text-transform: uppercase; font-size: 11px;">Email:</strong> <br> <span style="font-weight: 600; color: var(--agri-text-heading);">{{ $recommendation->user->email ?? '—' }}</span></p>
                    <p class="mb-0" style="font-size: 13px;"><strong style="color: var(--agri-text-muted); font-weight: 800; text-transform: uppercase; font-size: 11px;">Date:</strong> <br> <span style="font-weight: 600; color: var(--agri-text-heading);">{{ $recommendation->created_at->format('d M Y H:i') }}</span></p>
                </div>
            </div>
        </div>

        {{-- Input Params --}}
        <div class="col-md-4">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--agri-border); padding-bottom: 12px;">
                    <div style="width: 32px; height: 32px; background: #FEF3C7; color: #D97706; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sliders-h"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Input Parameters</h5>
                </div>
                <div>
                    @php
                        $params = [
                            'nitrogen' => $recommendation->nitrogen,
                            'phosphorus' => $recommendation->phosphorus,
                            'potassium' => $recommendation->potassium,
                            'temperature' => $recommendation->temperature,
                            'humidity' => $recommendation->humidity,
                            'ph_level' => $recommendation->ph_level,
                            'rainfall_mm' => $recommendation->rainfall_mm,
                        ];
                    @endphp
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($params as $key => $value)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px; border-bottom: 1px dashed var(--agri-border);">
                            <span style="font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase;">{{ str_replace('_', ' ', $key) }}</span>
                            <span style="font-size: 13px; font-weight: 700; color: var(--agri-text-heading);">{{ $value ?? 'N/A' }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Explanation --}}
        <div class="col-md-4">
            <div class="card-agri h-100" style="background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); padding: 24px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid var(--agri-border); padding-bottom: 12px;">
                    <div style="width: 32px; height: 32px; background: #DBEAFE; color: #1D4ED8; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">AI Explanation</h5>
                </div>
                <div>
                    <p class="mb-0" style="font-size: 13px; color: var(--agri-text-heading); line-height: 1.6;">{{ $recommendation->explanation ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recommended Crops Table --}}
    <div class="card-agri mt-4" style="padding: 0; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); overflow: hidden;">
        <div style="padding: 24px; border-bottom: 1px solid var(--agri-border); display: flex; align-items: center; gap: 12px;">
            <div style="width: 32px; height: 32px; background: #D1FAE5; color: #059669; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-list-ol"></i>
            </div>
            <h5 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 15px; text-transform: uppercase;">Recommended Crops (Ranked)</h5>
        </div>
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Rank</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Crop</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Confidence</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recommendation->recommended_crops ?? [] as $i => $crop)
                    <tr style="border-bottom: 1px solid var(--agri-border);">
                        <td style="padding: 16px 24px; font-weight: 800; color: var(--agri-text-muted);">#{{ $i + 1 }}</td>
                        <td style="padding: 16px 24px;">
                            @if($i === 0)
                                <span class="badge bg-success" style="padding: 6px 12px; border-radius: 8px; font-weight: 800;">{{ $crop['crop'] ?? ($crop['name'] ?? '—') }}</span>
                            @else
                                <span style="font-weight: 700; color: var(--agri-text-heading);">{{ $crop['crop'] ?? ($crop['name'] ?? '—') }}</span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="progress" style="height: 8px; width: 100px; border-radius: 4px; background: var(--agri-bg);">
                                    <div class="progress-bar bg-success" style="width:{{ $crop['confidence'] ?? 0 }}%; border-radius: 4px;"></div>
                                </div>
                                <span style="font-weight: 800; font-size: 12px; color: var(--agri-text-muted);">{{ $crop['confidence'] ?? 0 }}%</span>
                            </div>
                        </td>
                        <td style="padding: 16px 24px; font-size: 13px; color: var(--agri-text-muted);">{{ $crop['notes'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-muted text-center py-4">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
