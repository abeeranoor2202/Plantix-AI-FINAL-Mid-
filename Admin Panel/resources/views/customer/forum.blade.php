@extends('layouts.frontend')

@section('title', 'Community Forum | Plantix-AI')

@section('footer')
@include('partials.footer-alt')
@endsection

@section('page_scripts')
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    <script src="{{ asset('assets/js/experts.js') }}"></script>
    <script src="{{ asset('assets/js/dialogs.js') }}"></script>
    <script src="{{ asset('assets/js/toast.js') }}"></script>
    <script src="{{ asset('assets/js/strict-validation.js') }}"></script>
@endsection

@section('content')

    <!-- Start Breadcrumb -->
    <div class="py-4 bg-light" style="border-bottom: 1px solid var(--agri-border); background: linear-gradient(to right, rgba(35, 77, 32, 0.08), rgba(35, 77, 32, 0.02));">
        <div class="container-agri">
            <h1 class="fw-bold text-dark mb-2" style="font-size: 28px;">Community Forum</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="background: transparent; padding: 0; font-size: 14px;">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-success text-decoration-none"><i class="fas fa-home me-1"></i> Home</a></li>
                    <li class="breadcrumb-item active text-muted" aria-current="page">Forum</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div id="forum-page" class="py-5" style="background: var(--agri-bg); min-height: 70vh;">
        <div class="container-agri pb-5">
            
            <div class="row align-items-center mb-4">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <h2 class="fw-bold text-dark mb-2">Farmer's Discussion Board</h2>
                    <p class="text-muted m-0">Connect with other farmers and experts, share knowledge, and solve agricultural issues together.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="{{ route('forum.new') }}" class="btn-agri btn-agri-primary shadow-sm px-4">
                        <i class="fas fa-plus-circle me-2"></i> Start a Discussion
                    </a>
                </div>
            </div>

            <div class="card-agri p-4 border-0 shadow-sm">
                <!-- Forum Controls -->
                <div class="mb-4" style="padding: 0;">
                    <form method="GET" action="{{ route('forum') }}" class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Search Discussions</label>
                            <div style="position: relative;">
                                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--agri-text-muted);"></i>
                                <input type="text" name="search" class="form-agri" style="padding-left: 40px;" placeholder="Search threads, topics, or keywords..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Category</label>
                            <select name="category" class="form-agri">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Status</label>
                            <select name="status" class="form-agri">
                                <option value="">All Statuses</option>
                                @foreach(['open', 'resolved', 'locked', 'archived'] as $forumStatus)
                                    <option value="{{ $forumStatus }}" @selected(request('status') === $forumStatus)>{{ ucfirst($forumStatus) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">Sort</label>
                            <select name="sort_by" class="form-agri">
                                <option value="latest" @selected(request('sort_by') === 'latest')>Latest</option>
                                <option value="popular" @selected(request('sort_by') === 'popular')>Most Replies</option>
                                <option value="oldest" @selected(request('sort_by') === 'oldest')>Oldest</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">From</label>
                            <input type="date" name="date_from" class="form-agri" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label style="font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; margin-bottom: 8px; display: block;">To</label>
                            <input type="date" name="date_to" class="form-agri" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn-agri btn-agri-primary w-50" style="justify-content: center;">Filter</button>
                            <a href="{{ route('forum') }}" class="btn-agri btn-agri-outline w-50" style="justify-content: center; text-decoration: none;">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Forum Threads List -->
                <div style="padding: 0; overflow: hidden; border: 1px solid var(--agri-border); border-radius: 12px;">
                    <div style="padding: 24px 28px; border-bottom: 1px solid var(--agri-border); background: var(--agri-bg); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: var(--agri-primary-light); color: var(--agri-primary); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa fa-comments"></i></div>
                            <h6 style="margin: 0; font-weight: 800; color: var(--agri-text-heading); font-size: 14px; text-transform: uppercase;">All Threads</h6>
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
                                    <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--agri-text-muted); text-transform: uppercase; border: none; text-align: center;">Pinned</th>
                                </tr>
                            </thead>
                            <tbody style="background: white;">
                                @forelse($threads->items() as $thread)
                                <tr style="border-bottom: 1px solid var(--agri-border); transition: background 0.2s;" onmouseover="this.style.background='#F9FAFB'" onmouseout="this.style.background='white'">
                                    <td style="padding: 18px 24px;">
                                        <a href="{{ route('forum.thread', $thread->slug) }}" style="font-size: 14px; font-weight: 700; color: var(--agri-text-heading); text-decoration: none; display: block; max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ Str::limit($thread->title, 60) }}
                                        </a>
                                        <div style="font-size: 12px; color: var(--agri-text-muted); margin-top: 4px;">Posted {{ $thread->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td style="padding: 18px 24px; font-size: 13px; color: var(--agri-text-main); font-weight: 600;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="width: 24px; height: 24px; border-radius: 6px; background: var(--panel-primary-soft); color: var(--panel-primary-dark); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 800;">
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
                                        {{ $thread->replies->count() }}
                                    </td>
                                    <td style="padding: 18px 24px; text-align: center;">
                                        <x-platform.status-badge domain="forum" :status="$thread->status" />
                                    </td>
                                    <td style="padding: 18px 24px; text-align: center;">
                                        @if($thread->is_pinned)
                                            <i class="fa fa-thumbtack" style="color: #D97706;" title="Pinned"></i>
                                        @else
                                            <span style="color: var(--agri-text-muted);">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" style="padding: 60px 24px; text-align: center; border: none; background: white;">
                                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3 border" style="width: 90px; height: 90px;">
                                            <i class="far fa-comments fs-2 text-muted opacity-50"></i>
                                        </div>
                                        <h4 class="fw-bold text-dark">No Discussions Found</h4>
                                        <p class="text-muted small fw-medium mb-0">Try adjusting your search criteria or start a new discussion.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($threads->hasPages())
                <div class="mt-4 pt-4 border-top">
                    {{ $threads->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
