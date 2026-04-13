@extends('expert.layouts.app')
@section('title', 'Community Discussions')
@section('page-title', 'Farmer Community')

@section('content')
{{-- Filter/Search --}}
<div class="mb-4" style="padding: 0;">
    <form method="GET" action="{{ route('expert.forum.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Search Threads</label>
            <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                <input type="text" name="search" class="form-agri" style="padding-left: 40px;" placeholder="Search by topic, keyword, or crop..." value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3">
            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Category</label>
            <select name="category" class="form-agri">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" {{ ($filters['category'] ?? '') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
            <select name="status" class="form-agri">
                <option value="">All Statuses</option>
                <option value="open" {{ ($filters['status'] ?? '') === 'open' ? 'selected' : '' }}>Open</option>
                <option value="locked" {{ ($filters['status'] ?? '') === 'locked' ? 'selected' : '' }}>Locked</option>
                <option value="resolved" {{ ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="archived" {{ ($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn-agri btn-agri-primary w-50" style="justify-content: center;">Filter</button>
            <a href="{{ route('expert.forum.index') }}" class="btn-agri btn-agri-outline w-50" style="justify-content: center; text-decoration: none;">Reset</a>
        </div>
    </form>
</div>

{{-- Threads List --}}
<div style="padding: 0; overflow: hidden; border: 1px solid var(--agri-border); border-radius: 12px;">
    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); background: var(--agri-bg); display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-comments"></i></div>
            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">Recent Questions</h6>
        </div>
        <span style="background: white; border: 1px solid var(--agri-border); color: var(--agri-text-muted); padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 800;">{{ $threads->total() }} discussions</span>
    </div>

    <div class="table-responsive">
        <table class="table mb-0" style="vertical-align: middle; border-collapse: separate; border-spacing: 0;">
            <thead style="background: white; border-bottom: 1px solid var(--agri-border);">
                <tr>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Topic</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Author</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none;">Category</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Replies</th>
                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody style="background: white;">
                @forelse($threads->items() as $thread)
                <tr style="border-bottom: 1px solid var(--agri-border); transition: background 0.2s; cursor: pointer;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'" onclick="window.location='{{ route('expert.forum.show', $thread) }}'">
                    <td style="padding: 18px 24px;">
                        <a href="{{ route('expert.forum.show', $thread) }}" style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading); text-decoration: none; display: block; max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ Str::limit($thread->title, 60) }}
                        </a>
                        <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 4px;">Posted {{ $thread->created_at->diffForHumans() }}</div>
                    </td>
                    <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-main); font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 24px; height: 24px; border-radius: 6px; background: rgba(16, 185, 129, 0.1); color: var(--agri-primary); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
                                {{ strtoupper(substr($thread->user->name ?? 'F', 0, 1)) }}
                            </div>
                            {{ $thread->user->name ?? 'Farmer' }}
                        </div>
                    </td>
                    <td style="padding: 18px 24px;">
                        <span style="background: var(--agri-bg); border: 1px solid var(--agri-border); color: var(--agri-text-heading); padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700;">
                            {{ optional($thread->category)->name ?? 'General' }}
                        </span>
                    </td>
                    <td style="padding: 18px 24px; text-align: center; font-size: 14px; font-weight: 800; color: var(--agri-primary-dark);">
                        {{ $thread->replies_count ?? $thread->replies->count() }}
                    </td>
                    <td style="padding: 18px 24px; text-align: center;">
                        @php
                            $status = $thread->status ?? 'open';
                            $colors = [
                                'open'     => ['#D1FAE5', '#065F46'],
                                'resolved' => ['#E0F2FE', '#0369A1'],
                                'locked'   => ['#F3F4F6', '#4B5563'],
                                'archived' => ['#FEF3C7', '#92400E'],
                            ];
                            $c = $colors[$status] ?? ['#F9FAFB', '#6B7280'];
                        @endphp
                        <span style="background: {{ $c[0] }}; color: {{ $c[1] }}; padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 800; border: 1px solid {{ $c[0] }};">
                            {{ ucfirst($status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding: 60px 24px; text-align: center; border: none; background: white;">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border" style="width: 90px; height: 90px;">
                            <i class="far fa-comments fs-2 text-muted opacity-50"></i>
                        </div>
                        <h4 class="fw-bold text-dark">No Discussions Found</h4>
                        <p class="text-muted small fw-medium mb-0">Try adjusting your search criteria or check back later.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($threads->hasPages())
    <div class="mt-4 pt-4 border-top" style="background: white; padding: 20px;">
        {{ $threads->appends($filters)->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
