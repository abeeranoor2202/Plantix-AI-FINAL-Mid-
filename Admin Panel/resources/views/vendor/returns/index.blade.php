@extends('vendor.layouts.app')

@section('title', 'Return Requests')

@section('content')
<div class="container-fluid" style="padding-top: 24px; padding-bottom: 40px;">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap" style="gap: 12px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; margin: 0;">Return Requests</h1>
            <p class="text-muted mb-0">Review customer return requests and respond quickly with clear outcomes.</p>
        </div>
        <x-button :href="route('vendor.return-reasons.index')" variant="outline" icon="fas fa-sliders-h">Manage Return Reasons</x-button>
    </div>

    <x-card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <h4 class="mb-0 fw-bold text-dark" style="font-size: 18px;">Return Request List</h4>
                <form method="GET" action="{{ route('vendor.returns.index') }}" class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                    <div class="agri-search-wrap" style="width: 320px;">
                        <i class="fas fa-search agri-search-icon"></i>
                        <input type="text" name="search" class="form-agri agri-search-input" value="{{ $filters['search'] ?? '' }}" placeholder="Search order, customer, reason...">
                    </div>

                    <select name="status" class="form-agri" style="height: 42px; min-width: 150px; margin-bottom: 0;">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>

                    <x-button type="submit" variant="primary">Apply Filters</x-button>
                    <x-button :href="route('vendor.returns.index')" variant="outline">Clear</x-button>
                </form>
            </div>
        </x-slot>

        <x-table>
            <thead style="background: var(--agri-bg);">
                <tr>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Order</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Product</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Customer</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Return Reason</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Customer Message</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Date</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Status</th>
                    <th class="text-end" style="padding: 16px 24px; font-size: 12px; font-weight: 600; text-transform: uppercase; border: none;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    @php
                        $firstItem = $return->items->first();
                        $productName = $firstItem?->product?->name;
                        $extraCount = max(0, $return->items->count() - 1);
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="fw-bold text-dark">{{ $return->order->order_number ?? ('#' . $return->order_id) }}</div>
                            <small class="text-muted">Request #{{ $return->id }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-dark">{{ $productName ?: 'Product not available' }}</div>
                            @if($extraCount > 0)
                                <small class="text-muted">+{{ $extraCount }} more item{{ $extraCount > 1 ? 's' : '' }}</small>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="fw-semibold text-dark">{{ $return->user->name ?? 'Unknown customer' }}</div>
                            <small class="text-muted">{{ $return->user->email ?? 'No email' }}</small>
                        </td>
                        <td class="px-4 py-3">{{ $return->reason->name ?? 'Not selected' }}</td>
                        <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::limit($return->notes ?: 'No message provided.', 70) }}</td>
                        <td class="px-4 py-3">{{ $return->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <x-badge :variant="$return->status_badge_variant">{{ strtoupper(str_replace('_', ' ', $return->status)) }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-end d-flex justify-content-end" style="gap: 8px;">
                                <x-button :href="route('vendor.returns.show', $return->id)" variant="icon" title="View" style="width: 34px; height: 34px;">
                                    <i class="fas fa-eye"></i>
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-undo-alt fs-2 d-block mb-2"></i>
                                <div class="fw-bold">No return requests yet</div>
                                <div class="small">New return requests will appear here once customers submit them.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        @if($returns->hasPages())
            <div style="padding: 24px; border-top: 1px solid var(--agri-border); display: flex; justify-content: center;">
                {{ $returns->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </x-card>
</div>
@endsection

@push('styles')
<style>
    .agri-search-wrap {
        position: relative;
    }

    .agri-search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--agri-text-muted);
        font-size: 14px;
        pointer-events: none;
    }

    .agri-search-input {
        margin-bottom: 0;
        height: 42px;
        padding-left: 36px;
    }
</style>
@endpush
