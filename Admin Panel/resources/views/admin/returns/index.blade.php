@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">

    {{-- Header Section --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{url('/dashboard')}}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">{{trans('lang.dashboard')}}</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">After-Sales Service</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Returns & Refunds</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and manage customer return requests and financial reversals.</p>
        </div>
        <a href="{{ route('admin.returns.reasons') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 600;">
            <i class="fas fa-cog"></i> Configuration
        </a>
    </div>

    {{-- Filters Card --}}
    <div class="card-agri" style="padding: 24px; margin-bottom: 24px; background: white;">
        <form method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div style="position: relative;">
                    <i class="fas fa-filter" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted); font-size: 14px;"></i>
                    <select name="status" class="form-agri" style="padding-left: 40px; height: 44px;">
                        <option value="">Filter by Process Status</option>
                        @foreach(['pending','approved','rejected','refunded'] as $s)
                            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn-agri btn-agri-primary" style="flex: 1; height: 44px;">Filter Requests</button>
                @if(request()->has('status'))
                    <a href="{{ route('admin.returns.index') }}" class="btn-agri btn-agri-outline" style="padding: 10px; height: 44px; display: flex; align-items: center; text-decoration: none;">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Main Ledger --}}
    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--agri-border); background: white;">
            <h4 style="font-size: 16px; font-weight: 700; color: var(--agri-text-heading); margin: 0;">Post-Purchase Request Ledger</h4>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">ID & Order</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Customer Details</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Reason For Return</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;">Valuation</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-center">Current Status</th>
                        <th style="padding: 16px 24px; font-size: 11px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border: none;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr style="border-bottom: 1px solid var(--agri-border); transition: 0.2s;">
                        <td style="padding: 16px 24px;">
                            <div style="font-weight: 800; color: var(--agri-primary); font-size: 13px;">#R-{{ $return->id }}</div>
                            <a href="{{ route('admin.orders.show', $return->order_id) }}" style="text-decoration: none; font-size: 11px; font-weight: 700; color: var(--agri-text-muted);">Order #{{ $return->order_id }} <i class="fas fa-external-link-alt" style="font-size: 8px;"></i></a>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-weight: 700; color: var(--agri-text-heading); font-size: 14px;">{{ $return->user->name ?? 'External User' }}</div>
                            <div style="font-size: 11px; color: var(--agri-text-muted); font-weight: 500;">Requested on {{ $return->created_at->format('M d, Y') }}</div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="font-size: 13px; font-weight: 600; color: var(--agri-text-main); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $return->reason->reason ?? $return->reason_text ?? 'Not Specified' }}
                            </div>
                        </td>
                        <td style="padding: 16px 24px;">
                            @if($return->refund)
                                <div style="font-weight: 800; color: var(--agri-text-heading); font-size: 14px;">{{ config('plantix.currency_symbol') }}{{ number_format($return->refund->amount, 2) }}</div>
                                <div style="font-size: 10px; color: var(--agri-success); font-weight: 700; text-transform: uppercase;">Refund Processed</div>
                            @else
                                <div style="color: var(--agri-text-muted); font-size: 13px; font-weight: 600;">Pending Review</div>
                            @endif
                        </td>
                        <td style="padding: 16px 24px;" class="text-center">
                            @php
                                $statusMap = [
                                    'pending' => ['bg' => '#fffbeb', 'color' => '#d97706', 'icon' => 'clock'],
                                    'approved' => ['bg' => 'var(--agri-primary-light)', 'color' => 'var(--agri-primary)', 'icon' => 'check-circle'],
                                    'rejected' => ['bg' => '#FEF2F2', 'color' => 'var(--agri-error)', 'icon' => 'times-circle'],
                                    'refunded' => ['bg' => 'var(--agri-success-light)', 'color' => 'var(--agri-success)', 'icon' => 'wallet']
                                ];
                                $st = $statusMap[$return->status] ?? ['bg' => 'var(--agri-bg)', 'color' => 'var(--agri-text-muted)', 'icon' => 'info-circle'];
                            @endphp
                            <span style="background: {{ $st['bg'] }}; color: {{ $st['color'] }}; padding: 6px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fas fa-{{ $st['icon'] }}" style="font-size: 10px;"></i> {{ $return->status }}
                            </span>
                        </td>
                        <td style="padding: 16px 24px;" class="text-end">
                            <a href="{{ route('admin.returns.show', $return->id) }}" class="btn-agri" style="padding: 8px 16px; color: var(--agri-text-muted); background: var(--agri-bg); border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fas fa-eye"></i> Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div style="color: var(--agri-border); font-size: 48px; margin-bottom: 20px;"><i class="fas fa-undo"></i></div>
                            <div style="font-weight: 700; color: var(--agri-text-muted);">No return requests found in the system.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border);">
            {{ $returns->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
