@extends('layouts.app')

@section('title', 'Crop Plans')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Crop Plans</span>
            </div>
            <h2 class="h4 mb-0" style="font-weight: 700; color: var(--agri-primary-dark);"><i class="fas fa-calendar-check me-2 text-success"></i>Crop Plans</h2>
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
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Primary Crop</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Season</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Year</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Est. Revenue ({{ config('plantix.currency_symbol', 'PKR') }})</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 1px; border: none;">Created</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($plans as $plan)
                    <tr style="border-bottom: 1px solid var(--agri-border);">
                        <td style="padding: 16px 24px; font-weight: 600;">{{ $plan->id }}</td>
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-primary);">{{ $plan->user->name ?? '—' }}</td>
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-text-heading);">{{ $plan->primary_crop }}</td>
                        <td style="padding: 16px 24px;"><span style="background: var(--agri-bg); color: var(--agri-text-heading); font-weight: 800; font-size: 11px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; border: 1px solid var(--agri-border);">{{ $plan->season }}</span></td>
                        <td style="padding: 16px 24px; font-weight: 600;">{{ $plan->year }}</td>
                        <td style="padding: 16px 24px;">
                            <x-platform.status-badge domain="plan" :status="$plan->status" />
                        </td>
                        <td style="padding: 16px 24px; font-weight: 700; color: var(--agri-success);">{{ $plan->estimated_revenue ? config('plantix.currency_symbol', 'PKR') . ' ' . number_format($plan->estimated_revenue) : '—' }}</td>
                        <td style="padding: 16px 24px; color: var(--agri-text-muted); font-size: 13px;">{{ $plan->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No crop plans yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($plans->hasPages())
        <div style="padding: 24px; border-top: 1px solid var(--agri-border); background: var(--agri-bg);">
            {{ $plans->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
