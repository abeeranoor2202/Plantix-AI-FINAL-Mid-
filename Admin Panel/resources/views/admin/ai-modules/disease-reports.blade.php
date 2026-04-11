@extends('layouts.app')

@section('title', 'Disease Reports')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Disease Reports</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Crop Disease Intelligence</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Track detections, confidence scores, and moderation status at a glance.</p>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="btn-agri btn-agri-outline" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none; font-weight: 700;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> AI Dashboard
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Disease Report List</h4>
            <span class="badge rounded-pill bg-secondary">{{ $reports->total() }} Reports</span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">#</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">User</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Crop</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Detected Disease</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Confidence</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        <tr>
                            <td class="px-4 py-3">{{ $report->id }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-primary);">{{ $report->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $report->crop_name }}</td>
                            <td class="px-4 py-3">{!! $report->detected_disease ?? '<span style="color: var(--agri-text-muted); font-style: italic;">Pending</span>' !!}</td>
                            <td class="px-4 py-3">
                                @if($report->confidence_score)
                                    <span style="font-weight: 700;">{{ $report->confidence_percent }}%</span>
                                @else
                                    <span style="color: var(--agri-text-muted);">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php($st = strtolower((string) ($report->status ?? 'pending')))
                                <span class="badge rounded-pill {{ $st === 'verified' ? 'bg-success' : ($st === 'processed' ? 'bg-primary' : 'bg-warning text-dark') }}">{{ strtoupper($st) }}</span>
                            </td>
                            <td class="px-4 py-3">{{ $report->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.ai.disease-reports.show', $report->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5" style="color: var(--agri-text-muted);">No disease reports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $reports->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
