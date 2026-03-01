<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\CreateReplyRequest;
use App\Http\Requests\Forum\CreateThreadRequest;
use App\Http\Requests\Forum\FlagReplyRequest;
use App\Http\Requests\Forum\UpdateReplyRequest;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Services\Forum\ForumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ForumController — farmers, vendors (read-only), all authenticated users.
 *
 * Thin: validation in Form Requests, business logic in ForumService,
 * access control via Laravel Policies (authorize calls).
 *
 * Rate limits are applied at the route level (RouteServiceProvider).
 */
class ForumController extends Controller
{
    public function __construct(
        private readonly ForumService $forum,
    ) {}

    // ── Public (no auth required) ─────────────────────────────────────────────

    public function index(Request $request): View
    {
        $threads    = $this->forum->listThreads($request->only(['category', 'search', 'status']));
        $pinned     = $this->forum->pinnedThreads();
        $categories = ForumCategory::active()->withCount('threads')->get();

        return view('customer.forum', compact('threads', 'pinned', 'categories'));
    }

    public function show(string $slug): View
    {
        $thread = ForumThread::where('slug', $slug)
            ->where('is_approved', true)
            ->firstOrFail();

        $this->authorize('view', $thread);

        ['thread' => $thread, 'replies' => $replies] = $this->forum->showThread($thread);

        return view('customer.forum-thread', compact('thread', 'replies'));
    }

    // ── Thread: Create ────────────────────────────────────────────────────────

    public function create(): View
    {
        $this->authorize('create', ForumThread::class);

        $categories = ForumCategory::active()->get();

        return view('customer.forum-new', compact('categories'));
    }

    public function store(CreateThreadRequest $request): RedirectResponse
    {
        $this->authorize('create', ForumThread::class);

        $thread = $this->forum->createThread(auth('web')->user(), $request->validated());

        $message = $thread->is_approved
            ? 'Thread posted!'
            : 'Thread submitted — it will be visible after admin review.';

        return redirect()
            ->route('forum.thread', $thread->slug)
            ->with('success', $message);
    }

    // ── Reply: Create ─────────────────────────────────────────────────────────

    public function reply(CreateReplyRequest $request, ForumThread $thread): RedirectResponse
    {
        $this->authorize('create', [ForumReply::class, $thread]);

        try {
            $this->forum->createReply(auth('web')->user(), $thread, $request->validated());
        } catch (\DomainException $e) {
            return back()->withErrors(['body' => $e->getMessage()]);
        }

        return back()->with('success', 'Reply posted.');
    }

    // ── Reply: Edit ───────────────────────────────────────────────────────────

    public function editReply(UpdateReplyRequest $request, ForumReply $reply): RedirectResponse
    {
        $this->authorize('update', $reply);

        try {
            $this->forum->editReply($reply, $request->validated('body'));
        } catch (\DomainException $e) {
            return back()->withErrors(['body' => $e->getMessage()]);
        }

        return back()->with('success', 'Reply updated.');
    }

    // ── Reply: Soft Delete ────────────────────────────────────────────────────

    public function destroyReply(ForumReply $reply): RedirectResponse
    {
        $this->authorize('delete', $reply);

        $slug = $reply->thread->slug ?? $reply->thread_id;

        try {
            $this->forum->deleteReply(auth('web')->user(), $reply);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('forum.thread', $slug)
            ->with('success', 'Reply removed.');
    }

    // ── Reply: Flag ───────────────────────────────────────────────────────────

    public function flagReply(FlagReplyRequest $request, ForumReply $reply): RedirectResponse
    {
        $this->authorize('flag', $reply);

        try {
            $this->forum->flagReply(auth('web')->user(), $reply, $request->validated('reason'));
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Reply reported to moderators. Thank you.');
    }
}
