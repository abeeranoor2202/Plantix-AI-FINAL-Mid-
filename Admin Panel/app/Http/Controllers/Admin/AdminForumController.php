<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumFlag;
use App\Models\ForumLog;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use App\Services\Forum\ForumService;
use App\Services\Forum\ModerationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminForumController
 *
 * Admin-only moderation surface. Gates enforced via ForumThreadPolicy /
 * ForumReplyPolicy 'before' admin bypass — no role checks inside this class.
 *
 * Each action delegates to ModerationService. Controllers never touch
 * model state directly, never write ForumLog directly.
 */
class AdminForumController extends Controller
{
    public function __construct(
        private readonly ForumService      $forum,
        private readonly ModerationService $moderation,
    ) {}

    private function adminUser(): User
    {
        return auth('admin')->user() ?: abort(403);
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    /**
     * Dashboard / overview.
     */
    public function index(): View
    {
        $stats = [
            'total'    => ForumThread::count(),
            'open'     => ForumThread::where('status', ForumThread::STATUS_OPEN)->count(),
            'locked'   => ForumThread::where('status', ForumThread::STATUS_LOCKED)->count(),
            'resolved' => ForumThread::where('status', ForumThread::STATUS_RESOLVED)->count(),
            'archived' => ForumThread::where('status', ForumThread::STATUS_ARCHIVED)->count(),
            'pending'  => ForumThread::where('is_approved', false)->count(),
            'flags'    => ForumFlag::where('status', ForumFlag::STATUS_PENDING)->count(),
        ];

        $recentFlags = ForumFlag::with(['reply.thread', 'reporter'])
            ->where('status', ForumFlag::STATUS_PENDING)
            ->latest('created_at')
            ->take(20)
            ->get();

        $pendingThreads = ForumThread::with(['user', 'category'])
            ->where('is_approved', false)
            ->latest()
            ->take(20)
            ->get();

        return view('admin.forum.index', compact('stats', 'recentFlags', 'pendingThreads'));
    }

    // ── Thread List ───────────────────────────────────────────────────────────

    public function threads(Request $request): View
    {
        $query = ForumThread::with(['user', 'category'])
            ->withCount('allReplies as replies_count');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('forum_category_id')) {
            $query->where('forum_category_id', $request->forum_category_id);
        }

        if ($request->filled('approved')) {
            $query->where('is_approved', (bool) $request->approved);
        }

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(fn ($q) => $q
                ->where('title', 'like', $search)
                ->orWhere('body', 'like', $search)
            );
        }

        $threads    = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $categories = ForumCategory::orderBy('name')->get();
        $statuses   = [
            ForumThread::STATUS_OPEN,
            ForumThread::STATUS_LOCKED,
            ForumThread::STATUS_RESOLVED,
            ForumThread::STATUS_ARCHIVED,
        ];

        return view('admin.forum.threads', compact('threads', 'categories', 'statuses'));
    }

    public function showThread(int $id): View
    {
        $thread = ForumThread::with([
            'user',
            'category',
            'resolvedReply.user',
            'allReplies' => fn ($q) => $q
                ->with(['user', 'children.user', 'flags'])
                ->whereNull('parent_id')
                ->withTrashed(),
        ])->withTrashed()->findOrFail($id);

        $logs = ForumLog::with('user')
            ->where('thread_id', $id)
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return view('admin.forum.thread-show', compact('thread', 'logs'));
    }

    // ── Thread Actions ────────────────────────────────────────────────────────

    public function approveThread(int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'approve', $thread);

        $this->moderation->approveThread($admin, $thread);

        return back()->with('success', 'Thread approved and now visible.');
    }

    public function lockThread(Request $request, int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'lock', $thread);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $this->moderation->lockThread($admin, $thread, $request->reason);

        return back()->with('success', 'Thread locked.');
    }

    public function unlockThread(int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'lock', $thread);

        $this->moderation->unlockThread($admin, $thread);

        return back()->with('success', 'Thread unlocked.');
    }

    public function resolveThread(Request $request, int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'resolve', $thread);

        $request->validate(['reply_id' => 'required|integer|exists:forum_replies,id']);

        try {
            $this->moderation->resolveThread(
                $admin,
                $thread,
                ForumReply::findOrFail($request->reply_id)
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Thread marked as resolved.');
    }

    public function archiveThread(int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'archive', $thread);

        $this->moderation->archiveThread($admin, $thread);

        return back()->with('success', 'Thread archived.');
    }

    public function pinThread(int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'pin', $thread);

        $newState = $this->moderation->togglePin($admin, $thread);

        return back()->with('success', $newState ? 'Thread pinned.' : 'Thread unpinned.');
    }

    public function destroyThread(int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'delete', $thread);

        $this->moderation->deleteThread($admin, $thread);

        return redirect()
            ->route('admin.forum.threads')
            ->with('success', 'Thread deleted.');
    }

    public function changeCategory(Request $request, int $id): RedirectResponse
    {
        $thread = ForumThread::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'update', $thread);

        $request->validate(['forum_category_id' => 'required|integer|exists:forum_categories,id']);

        $this->moderation->changeCategory($admin, $thread, $request->forum_category_id);

        return back()->with('success', 'Category updated.');
    }

    // ── Reply Actions ─────────────────────────────────────────────────────────

    public function destroyReply(int $id): RedirectResponse
    {
        $reply = ForumReply::withTrashed()->findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'delete', $reply);

        $this->moderation->deleteReply($admin, $reply);

        return back()->with('success', 'Reply deleted.');
    }

    public function removeOfficialAnswer(int $id): RedirectResponse
    {
        $reply = ForumReply::findOrFail($id);
        $admin = $this->adminUser();
        $this->authorizeForUser($admin, 'removeOfficial', $reply);

        $this->forum->removeOfficialAnswer($admin, $reply);

        return back()->with('success', 'Official answer status removed.');
    }

    // ── Flags ─────────────────────────────────────────────────────────────────

    public function flags(Request $request): View
    {
        $query = ForumFlag::with(['reply.thread', 'reporter', 'reviewer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $flags = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('admin.forum.flags', compact('flags'));
    }

    public function dismissFlag(int $id): RedirectResponse
    {
        $this->moderation->dismissFlag($this->adminUser(), ForumFlag::findOrFail($id));

        return back()->with('success', 'Flag dismissed.');
    }

    public function confirmFlag(int $id): RedirectResponse
    {
        $this->moderation->confirmFlag($this->adminUser(), ForumFlag::findOrFail($id));

        return back()->with('success', 'Flag confirmed. Reply remains flagged.');
    }

    // ── Ban Management ────────────────────────────────────────────────────────

    public function banUser(Request $request, int $userId): RedirectResponse
    {
        $target = User::findOrFail($userId);
        $admin = $this->adminUser();

        $data = $request->validate([
            'reason'       => 'required|string|max:500',
            'banned_until' => 'nullable|date|after:now',
            'shadow'       => 'boolean',
        ]);

        try {
            $this->moderation->banUser(
                $admin,
                $target,
                $data['reason'],
                isset($data['banned_until']) ? \Carbon\Carbon::parse($data['banned_until']) : null,
                (bool) ($data['shadow'] ?? false)
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "User '{$target->name}' has been banned.");
    }

    public function unbanUser(int $userId): RedirectResponse
    {
        $target = User::findOrFail($userId);
        $this->moderation->unbanUser($this->adminUser(), $target);

        return back()->with('success', "User '{$target->name}' has been unbanned.");
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function categories(): View
    {
        $categories = ForumCategory::withCount('threads')->orderBy('sort_order')->get();

        return view('admin.forum.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:forum_categories,name',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        ForumCategory::create($data);

        return redirect()
            ->route('admin.forum.categories.index')
            ->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, int $id): RedirectResponse
    {
        $category = ForumCategory::findOrFail($id);

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:forum_categories,name,' . $id,
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:50',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        $category->update($data);

        return redirect()
            ->route('admin.forum.categories.index')
            ->with('success', 'Category updated.');
    }

    public function destroyCategory(int $id): RedirectResponse
    {
        $category = ForumCategory::withCount('threads')->findOrFail($id);

        if ($category->threads_count > 0) {
            return back()->withErrors([
                'category' => "Cannot delete: {$category->threads_count} thread(s) are using this category.",
            ]);
        }

        $category->delete();

        return redirect()
            ->route('admin.forum.categories.index')
            ->with('success', 'Category deleted.');
    }

    // ── Audit Log ─────────────────────────────────────────────────────────────

    public function auditLog(Request $request): View
    {
        $query = ForumLog::with(['user', 'thread', 'reply']);

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs    = $query->orderByDesc('created_at')->paginate(50)->withQueryString();
        $actions = ForumLog::select('action')->distinct()->pluck('action')->toArray();

        return view('admin.forum.audit-log', compact('logs', 'actions'));
    }
}
