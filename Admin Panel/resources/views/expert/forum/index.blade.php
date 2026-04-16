@extends('expert.layouts.app')

@section('title', 'Forum')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px;">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <a href="{{ route('expert.dashboard') }}" style="text-decoration: none; color: var(--agri-text-muted); font-size: 14px; font-weight: 600;">Dashboard</a>
            <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--agri-text-muted);"></i>
            <span style="color: var(--agri-primary); font-size: 14px; font-weight: 600;">Forum</span>
        </div>
        <h1 style="font-size: 28px; font-weight: 700; color: var(--agri-primary-dark); margin: 0;">Forum Threads</h1>
        <p style="color: var(--agri-text-muted); margin: 4px 0 0 0;">Review farmer discussions and provide expert responses.</p>
    </div>
</div>

<div class="card-agri mb-4" style="padding: 20px 24px;">
    <form method="GET" action="{{ route('expert.forum.index') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="agri-label">Search</label>
                <x-input name="search" :value="$filters['search'] ?? ''" placeholder="Title or body" />
            </div>
            <div class="col-md-3">
                <label class="agri-label">Category</label>
                <select name="category" class="form-agri">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" {{ ($filters['category'] ?? '') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="agri-label">Status</label>
                <select name="status" class="form-agri">
                    <option value="">All</option>
                    @foreach(['open','locked','resolved','archived'] as $st)
                        <option value="{{ $st }}" {{ ($filters['status'] ?? '') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <x-button type="submit" variant="primary" icon="fas fa-filter" class="w-100">Apply</x-button>
                <x-button :href="route('expert.forum.index')" variant="outline" class="w-100">Clear Filters</x-button>
            </div>
        </div>
    </form>
</div>

<div class="card-agri" style="padding: 0; overflow: hidden;">
    <x-table>
        <thead style="background: var(--agri-bg);">
            <tr>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">TOPIC</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">AUTHOR</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">CATEGORY</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">REPLIES</th>
                <th style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">STATUS</th>
                <th class="text-end" style="padding: 16px 24px; font-size: 12px; color: var(--agri-text-muted); border: none;">ACTIONS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($threads->items() as $thread)
                <tr>
                    <td class="px-4 py-3">
                        <div style="font-weight: 700; color: var(--agri-text-heading);">{{ Str::limit($thread->title, 70) }}</div>
                        <small class="text-muted">{{ $thread->created_at->diffForHumans() }}</small>
                    </td>
                    <td class="px-4 py-3">{{ $thread->user->name ?? 'Farmer' }}</td>
                    <td class="px-4 py-3">{{ optional($thread->category)->name ?? 'General' }}</td>
                    <td class="px-4 py-3">{{ $thread->replies_count ?? 0 }}</td>
                    <td class="px-4 py-3">
                        @php
                            $statusVariant = match($thread->status ?? 'open') {
                                'open', 'resolved' => 'success',
                                'locked' => 'warning',
                                'archived' => 'danger',
                                default => 'secondary',
                            };
                        @endphp
                        <x-badge :variant="$statusVariant">{{ ucfirst($thread->status ?? 'open') }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-end">
                        <div style="display: inline-flex; gap: 8px;">
                            <a href="{{ route('expert.forum.show', $thread) }}" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #2563eb; border-radius: 999px;" title="View"><i class="fas fa-eye"></i></a>
                            <button type="button" class="btn-agri" style="padding: 8px; background: var(--agri-bg); color: #9ca3af; border-radius: 999px; border: none;" title="Edit unavailable" disabled><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn-agri" style="padding: 8px; background: #fef2f2; color: #fca5a5; border-radius: 999px; border: none;" title="Delete unavailable" disabled><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5" style="color: var(--agri-text-muted);">
                        <i class="mdi mdi-forum-outline" style="font-size: 28px; display:block; margin-bottom: 8px; opacity: .5;"></i>
                        No discussions found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>
</div>

@if($threads->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: center;">
        {{ $threads->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
@endif
@endsection
