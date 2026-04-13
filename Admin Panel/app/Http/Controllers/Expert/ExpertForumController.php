<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\PostExpertReplyRequest;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Services\Forum\ForumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * ExpertForumController
 *
 * Expert participation in the forum:
 *  - Browse / view threads
 *  - Post replies (auto-tagged as expert reply)
 *  - Mark a reply as the Official Answer (one per thread, race-condition safe)
 *
 * Business logic: ForumService.
 * Access control: ForumThreadPolicy + ForumReplyPolicy via $this->authorize().
 */
class ExpertForumController extends Controller
{
    public function __construct(
        private readonly ForumService $forum,
    ) {}

    // ── Thread List ───────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $filters = $request->only(['category', 'search', 'status']);
        $threads = $this->forum->listThreads($filters);
        $categories = ForumCategory::active()->withCount('threads')->get();

        return view('expert.forum.index', compact('threads', 'filters', 'categories'));
    }

    public function show(ForumThread $thread): View
    {
        Gate::forUser(auth('expert')->user())->authorize('view', $thread);

        ['thread' => $thread, 'replies' => $replies] = $this->forum->showThread($thread);

        return view('expert.forum.show', compact('thread', 'replies'));
    }

    // ── Reply ─────────────────────────────────────────────────────────────────

    public function reply(PostExpertReplyRequest $request, ForumThread $thread): RedirectResponse
    {
        Gate::forUser(auth('expert')->user())->authorize('create', [ForumReply::class, $thread]);

        $author = auth('expert')->user();

        try {
            // Mark as expert reply via direct flag on the reply data
            $data         = $request->validated();
            $data['is_expert_reply'] = false; // set through create in service explicitly
            // Build via service using createReply — expert flag set in next step
            $reply = $this->forum->createReply($author, $thread, $data);

            // Tag as expert reply (service intentionally keeps this out of fillable)
            $reply->update([
                'is_expert_reply' => true,
                'expert_id'       => $author->expert?->id,
            ]);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('expert.forum.show', $thread)
            ->with('success', 'Your expert reply has been posted.');
    }

    // ── Mark Official Answer ──────────────────────────────────────────────────

    public function markOfficial(ForumReply $reply): RedirectResponse
    {
        Gate::forUser(auth('expert')->user())->authorize('markOfficial', $reply);

        try {
            $this->forum->markOfficialAnswer(auth('expert')->user(), $reply);
        } catch (\DomainException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Reply marked as the Official Answer.');
    }
}
