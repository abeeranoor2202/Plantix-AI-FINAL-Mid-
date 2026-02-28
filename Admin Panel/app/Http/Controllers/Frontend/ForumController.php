<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    public function index(Request $request): View
    {
        $query = ForumThread::with(['user', 'category', 'replies'])
                            ->approved()
                            ->latest();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $threads    = $query->paginate(15)->withQueryString();
        $categories = ForumCategory::active()->withCount('threads')->get();

        return view('customer.forum', compact('threads', 'categories'));
    }

    public function show(int $id): View
    {
        $thread = ForumThread::with(['user', 'replies.user'])
                             ->approved()
                             ->findOrFail($id);

        $thread->incrementViews();

        return view('customer.forum-thread', compact('thread'));
    }

    public function create(): View
    {
        $this->requireAuth();
        $categories = ForumCategory::active()->get();
        return view('customer.forum-new', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireAuth();

        $request->validate([
            'title'            => 'required|string|min:5|max:255',
            'body'             => 'required|string|min:20|max:10000',
            'forum_category_id'=> 'nullable|exists:forum_categories,id',
        ]);

        $thread = ForumThread::create([
            'user_id'          => auth('web')->id(),
            'forum_category_id'=> $request->forum_category_id,
            'title'            => strip_tags($request->title),
            'body'             => htmlspecialchars(strip_tags($request->body), ENT_QUOTES, 'UTF-8'),
            'is_approved'      => true, // auto-approve for now; can add moderation toggle
        ]);

        return redirect()->route('forum.thread', $thread->id)
                         ->with('success', 'Thread posted!');
    }

    public function reply(Request $request, int $threadId): RedirectResponse
    {
        $this->requireAuth();

        $request->validate(['body' => 'required|string|min:5|max:5000']);

        $thread = ForumThread::findOrFail($threadId);

        if ($thread->is_locked) {
            return back()->withErrors(['body' => 'This thread is locked.']);
        }

        ForumReply::create([
            'thread_id'   => $thread->id,
            'user_id'     => auth('web')->id(),
            'body'        => htmlspecialchars(strip_tags($request->body), ENT_QUOTES, 'UTF-8'),
            'is_approved' => true,
        ]);

        return back()->with('success', 'Reply posted.');
    }

    private function requireAuth(): void
    {
        if (! auth('web')->check()) {
            abort(redirect()->route('signin'));
        }
    }
}
