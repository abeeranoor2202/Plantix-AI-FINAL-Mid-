@extends('layouts.app')

@section('title', 'Disease Reports')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Disease Reports</span>
            </div>
            <h2 class="h4 mb-0" style="font-weight: 700; color: var(--agri-primary-dark);"><i class="fas fa-virus me-2 text-warning"></i>Crop Disease Intelligence</h2>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: 700; padding: 10px 20px;">
            <i class="fas fa-arrow-left"></i> AI Dashboard
        </a>
    </div>

    <div class="card-agri" style="padding: 0; background: white; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.04); overflow: hidden;">
        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">#</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">User</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Crop</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Detected Disease</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Confidence</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Date</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($reports as $report)
                    <tr style="border-bottom: 1px solid var(--agri-border);">
                        <td style="padding: 16px 24px; font-weight: 600;">{{ $report->id }}</td>
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-primary);">{{ $report->user->name ?? '—' }}</td>
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading);">{{ $report->crop_name }}</td>
                        <td style="padding: 16px 24px; font-weight: 600;">{!! $report->detected_disease ?? '<span style="color: var(--agri-text-muted); font-style: italic;">Pending</span>' !!}</td>
                        <td style="padding: 16px 24px;">
                            @if($report->confidence_score)
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="progress" style="height: 6px; width: 60px; border-radius: 4px; background: var(--agri-bg);">
                                        <div class="progress-bar bg-warning" style="width:{{ $report->confidence_percent }}%; border-radius: 4px;"></div>
                                    </div>
                                    <span style="font-weight: 800; font-size: 12px; color: var(--agri-text-muted);">{{ $report->confidence_percent }}%</span>
                                </div>
                            @else
                                <span style="color: var(--agri-text-muted);">—</span>
                            @endif
                        </td>
                        <td style="padding: 16px 24px;">
                            @php
                                $statusColors = [
                                    'verified' => ['bg' => '#D1FAE5', 'text' => '#065F46', 'border' => '#A7F3D0'],
                                    'processed' => ['bg' => '#DBEAFE', 'text' => '#1D4ED8', 'border' => '#BFDBFE'],
                                    'pending' => ['bg' => '#FEF3C7', 'text' => '#B45309', 'border' => '#FDE68A']
                                ];
                                $st = $report->status ?? 'pending';
                                $colors = $statusColors[$st] ?? ['bg' => '#F3F4F6', 'text' => '#374151', 'border' => '#E5E7EB'];
                            @endphp
                            <span style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; border: 1px solid {{ $colors['border'] }}; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 900; text-transform: uppercase;">
                                    {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px; color: var(--agri-text-muted); font-size: 13px;">{{ $report->created_at->format('d M Y') }}</td>
                        <td style="padding: 16px 24px;" class="text-end">
                            <a href="{{ route('admin.ai.disease-reports.show', $report->id) }}"
                               class="btn-agri btn-agri-primary" style="padding: 8px 16px; font-size: 12px; font-weight: 700; text-decoration: none;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No disease reports yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        @if($reports->hasPages())
        <div style="padding: 24px; border-top: 1px solid var(--agri-border); background: var(--agri-bg);">
            {{ $reports->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
