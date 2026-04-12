@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="{{ url('/dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
                <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
                <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Returns</span>
            </div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Returns & Refunds</h1>
            <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review and manage return requests with consistent approval tracking.</p>
        </div>
        <a href="{{ route('admin.returns.reasons') }}" class="btn-agri btn-agri-outline" style="text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 700;">
            <i class="fas fa-cog"></i> Configuration
        </a>
    </div>

    <div class="card-agri" style="padding: 0; overflow: hidden;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center" style="gap: 10px; flex-wrap: wrap;">
            <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Return Request List</h4>
            <form method="GET" action="{{ route('admin.returns.index') }}" style="display: flex; align-items: center; gap: 10px;">
                <select name="status" class="form-agri" style="height: 42px; min-width: 180px; margin-bottom: 0;">
                    <option value="">All Statuses</option>
                    @foreach(['pending','approved','rejected','refund_processing','completed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-agri btn-agri-primary" style="height: 42px; padding: 0 16px;">Filter</button>
                @if(request()->has('status'))
                    <a href="{{ route('admin.returns.index') }}" class="btn-agri btn-agri-outline" style="height: 42px; display: inline-flex; align-items: center; padding: 0 14px; text-decoration: none;">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table mb-0" style="vertical-align: middle;">
                <thead style="background: var(--agri-bg);">
                    <tr>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Return ID</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Order</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Customer</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Reason</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Refund</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Status</th>
                        <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                        <tr>
                            <td class="px-4 py-3">
                                <div style="font-weight: 700; color: var(--agri-primary-dark);">#R-{{ $return->id }}</div>
                                <small class="text-muted">{{ $return->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.orders.show', $return->order_id) }}" style="text-decoration: none; color: var(--agri-primary); font-weight: 700;">#{{ $return->order_id }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $return->user->name ?? 'External User' }}</td>
                            <td class="px-4 py-3">{{ \Illuminate\Support\Str::limit($return->reason->title ?? $return->reason->reason ?? $return->notes ?? 'Not Specified', 60) }}</td>
                            <td class="px-4 py-3">
                                @if($return->refund)
                                    <span style="font-weight: 700; color: var(--agri-text-heading);">{{ config('plantix.currency_symbol') }}{{ number_format($return->refund->amount, 2) }}</span>
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php($st = strtolower((string) $return->status))
                                <span class="badge rounded-pill {{ in_array($st, ['completed']) ? 'bg-success' : ($st === 'approved' ? 'bg-info' : ($st === 'pending' ? 'bg-warning text-dark' : ($st === 'refund_processing' ? 'bg-primary' : 'bg-danger'))) }}">
                                    {{ strtoupper($st) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-end" style="display: flex; justify-content: flex-end; gap: 8px;">
                                    <a href="{{ route('admin.returns.show', $return->id) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5" style="color: var(--agri-text-muted);">No return requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
            <div style="padding: 24px; background: white; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $returns->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
