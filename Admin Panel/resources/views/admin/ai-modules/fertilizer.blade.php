@extends('layouts.app')

@section('title', 'Fertilizer Recommendations')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ route('admin.ai.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">AI Agriculture</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Fertilizer</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Fertilizer Recommendations</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">NPK recommendations and projected input cost from AI runs.</p>
        </div>
        <a href="{{ route('admin.ai.dashboard') }}" class="btn-agri btn-agri-outline" style="height: 42px; display: inline-flex; align-items: center; text-decoration: none; font-weight: 700;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> AI Dashboard
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Fertilizer Recommendation List</h4>
            <span class="badge rounded-pill bg-success">{{ $recommendations->total() }} Records</span>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">#</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">User</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Crop</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Growth Stage</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">N</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">P</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">K</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Est. Cost ({{ config('plantix.currency_symbol', 'PKR') }})</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recommendations as $rec)
                        <tr>
                            <td class="px-4 py-3">{{ $rec->id }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-primary);">{{ $rec->user->name ?? '—' }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-text-heading);">{{ $rec->crop_type }}</td>
                            <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $rec->growth_stage ?? '')) }}</td>
                            <td class="px-4 py-3">{{ $rec->n_recommendation ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $rec->p_recommendation ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $rec->k_recommendation ?? '—' }}</td>
                            <td class="px-4 py-3" style="font-weight: 700; color: var(--agri-success);">{{ $rec->estimated_cost ? config('plantix.currency_symbol', 'PKR').' '.number_format($rec->estimated_cost) : '—' }}</td>
                            <td class="px-4 py-3">{{ $rec->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5" style="color: var(--agri-text-muted);">No fertilizer recommendations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($recommendations->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $recommendations->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
