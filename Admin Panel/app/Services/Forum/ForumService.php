<?php

namespace App\Services\Forum;

use App\Models\ForumFlag;
use App\Models\ForumLog;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\Forum\ForumReplyPostedNotification;
use App\Notifications\Forum\OfficialAnswerNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ForumService
 *
 * Single source of truth for all forum business logic.
 * Controllers are thin — they validate, delegate here, return responses.
 *
 * Thread lifecycle: open → locked | resolved → archived (admin can override)
 * Official answer:  one per thread, set by expert or admin under DB lock.
 * Reply nesting:    max depth = ForumReply::MAX_DEPTH (1 = one level of children).
 */
class ForumService
{
    // ── Thread Operations ─────────────────────────────────────────────────────

    /**
     * Create a new forum thread, generating a collision-safe unique slug.
     *
     * @param  array{title: string, body: string, forum_category_id: ?int}  $data
     */
    public function createThread(User $author, array $data): ForumThread
    {
        $autoApprove = (bool) config('plantix.forum_auto_approve', true);

        $thread = DB::transaction(function () use ($author, $data, $autoApprove): ForumThread {
            $slug = ForumThread::generateSlug($data['title']);

            $thread = ForumThread::create([
                'user_id'           => $author->id,
                'forum_category_id' => $data['forum_category_id'] ?? null,
                'title'             => $data['title'],
                'slug'              => $slug,
                'body'              => $this->sanitize($data['body']),
                'status'            => ForumThread::STATUS_OPEN,
                'is_approved'       => $autoApprove,
                'is_pinned'         => false,
                'views'             => 0,
                'replies_count'     => 0,
            ]);

            ForumLog::record($author->id, ForumLog::ACTION_THREAD_CREATE, $thread->id);

            return $thread;
        });

        Cache::forget('forum.pinned_threads');
        Cache::forget("forum.category_counts");

        return $thread;
    }

    /**
     * List threads for the public/authenticated index with cache-assisted pinned threads.
     *
     * @param  array{category?: string, search?: string, status?: string, date_from?: string, date_to?: string, sort_by?: string}  $filters
     */
    public function listThreads(array $filters = []): LengthAwarePaginator
    {
        $query = ForumThread::with(['user:id,name,profile_photo', 'category:id,name,slug'])
            ->withCount(['allReplies as replies_count_live'])
            ->where('is_approved', true)
            ->where('status', '!=', ForumThread::STATUS_ARCHIVED);

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $filters['category']));
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($searchQuery) use ($term): void {
                $searchQuery->where('title', 'like', $term . '%')
                    ->orWhere('title', 'like', '% ' . $term . '%')
                    ->orWhere('body', 'like', '%' . $term . '%');
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $sortBy = $filters['sort_by'] ?? 'latest';

        if ($sortBy === 'popular') {
            $query->orderByDesc('replies_count');
        } elseif ($sortBy === 'oldest') {
            $query->orderBy('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        // Pinned threads first, then newest
        return $query->orderByDesc('is_pinned')
                     ->paginate(15)
                     ->withQueryString();
    }

    /**
     * Return pinned open threads from cache (15-min TTL).
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function pinnedThreads(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('forum.pinned_threads', 900, function () {
            return ForumThread::with(['user:id,name', 'category:id,name,slug'])
                ->where('is_approved', true)
                ->where('is_pinned', true)
                ->where('status', ForumThread::STATUS_OPEN)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        });
    }

    /**
     * Load a single thread for the show page.
     * Increments views atomically.
     * Replies are paginated at 20 per page, nested children eager-loaded.
     *
     * @return array{thread: ForumThread, replies: LengthAwarePaginator}
     */
    public function showThread(ForumThread $thread): array
    {
        $thread->incrementViews();

        $thread->loadMissing(['user:id,name,profile_photo', 'category:id,name,slug', 'resolvedReply']);

        $replies = ForumReply::with([
                'user:id,name,profile_photo,role',
                'children' => fn ($q) => $q
                    ->with('user:id,name,profile_photo,role')
                    ->visible()
                    ->orderBy('created_at'),
            ])
            ->where('thread_id', $thread->id)
            ->topLevel()
            ->visible()
            ->orderBy('is_official', 'desc')  // official answer floats to top
            ->orderBy('created_at')
            ->paginate(20)
            ->withQueryString();

        return compact('thread', 'replies');
    }

    // ── Reply Operations ──────────────────────────────────────────────────────

    /**
     * Create a reply (top-level or nested).
     *
     * @param  array{body: string, parent_id?: int|null}  $data
     */
    public function createReply(User $author, ForumThread $thread, array $data): ForumReply
    {
        // Validate nesting depth
        if (! empty($data['parent_id'])) {
            $parent = ForumReply::findOrFail($data['parent_id']);

            if ($parent->thread_id !== $thread->id) {
                throw new \DomainException('Parent reply does not belong to this thread.');
            }

            if ($parent->parent_id !== null) {
                throw new \DomainException('Maximum reply nesting depth exceeded.');
            }

            // Cannot reply to a deleted or flagged-without-body parent
            if ($parent->trashed()) {
                throw new \DomainException('Cannot reply to a deleted comment.');
            }
        }

        $autoApprove = (bool) config('plantix.forum_auto_approve', true);

        $reply = DB::transaction(function () use ($author, $thread, $data, $autoApprove): ForumReply {
            $reply = ForumReply::create([
                'thread_id'   => $thread->id,
                'user_id'     => $author->id,
                'parent_id'   => $data['parent_id'] ?? null,
                'body'        => $this->sanitize($data['body']),
                'status'      => ForumReply::STATUS_VISIBLE,
                'is_approved' => $autoApprove,
            ]);

            // Maintain replies_count counter cache
            $thread->incrementRepliesCount();

            ForumLog::record($author->id, ForumLog::ACTION_REPLY_CREATE, $thread->id, $reply->id);

            return $reply;
        });

        // Notify thread owner — must be queued via ShouldQueue on the notification
        if ($autoApprove && $thread->user_id !== $author->id) {
            $thread->loadMissing('user');
            if ($thread->user) {
                try {
                    $thread->user->notify(new ForumReplyPostedNotification($reply, $thread));
                } catch (\Throwable $e) {
                    Log::warning('forum.notify.reply_posted failed', ['error' => $e->getMessage()]);
                }
            }
        }

        return $reply;
    }

    /**
     * Edit a reply body within the edit window.
     */
    public function editReply(ForumReply $reply, string $newBody): ForumReply
    {
        if (! $reply->isEditable()) {
            throw new \DomainException('Edit window has expired. Replies cannot be edited after 15 minutes.');
        }

        $reply->update([
            'body'      => $this->sanitize($newBody),
            'edited_at' => now(),
        ]);

        ForumLog::record(
            $reply->user_id,
            ForumLog::ACTION_REPLY_EDIT,
            $reply->thread_id,
            $reply->id,
            ['old_body_hash' => md5($reply->getOriginal('body'))]
        );

        return $reply->fresh();
    }

    /**
     * Soft-delete a reply (owner or admin).
     * If the reply was the thread's resolved_reply, clear that link.
     */
    public function deleteReply(User $actor, ForumReply $reply): void
    {
        DB::transaction(function () use ($actor, $reply): void {
            // Clear official + resolved_reply_id if this was the official answer
            if ($reply->is_official) {
                $this->clearOfficialAnswer($reply->thread_id, $reply->id);
            }

            $reply->delete();

            // Decrement counter cache
            $reply->thread->decrementRepliesCount();

            ForumLog::record($actor->id, ForumLog::ACTION_REPLY_DELETE, $reply->thread_id, $reply->id);
        });
    }

    // ── Official Answer ───────────────────────────────────────────────────────

    /**
     * Mark a reply as the official answer for its thread.
     *
     * Race condition protection:
     *  - SELECT ... FOR UPDATE locks the thread row
     *  - Clears any existing official answer atomically
     *  - Sets is_official on the new reply
     * All inside a single serialisable transaction.
     */
    public function markOfficialAnswer(User $actor, ForumReply $reply): void
    {
        DB::transaction(function () use ($actor, $reply): void {
            // Lock the thread row to prevent concurrent official-answer marking
            /** @var ForumThread $thread */
            $thread = ForumThread::lockForUpdate()->findOrFail($reply->thread_id);

            if (! $thread->isOpen()) {
                throw new \DomainException('Cannot mark official answer on a ' . $thread->status . ' thread.');
            }

            // Clear existing official answer (if any)
            ForumReply::where('thread_id', $thread->id)
                ->where('is_official', true)
                ->update(['is_official' => false]);

            // Mark new official answer
            $reply->update(['is_official' => true]);

            ForumLog::record(
                $actor->id,
                ForumLog::ACTION_REPLY_OFFICIAL,
                $thread->id,
                $reply->id,
                ['actor_role' => $actor->role]
            );
        });

        // Notify thread owner
        $reply->loadMissing(['thread.user']);
        $thread = $reply->thread;
        if ($thread && $thread->user && $thread->user_id !== $actor->id) {
            try {
                $thread->user->notify(new OfficialAnswerNotification($reply, $thread));
            } catch (\Throwable $e) {
                Log::warning('forum.notify.official_answer failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Remove official status from a reply (admin only).
     */
    public function removeOfficialAnswer(User $actor, ForumReply $reply): void
    {
        DB::transaction(function () use ($actor, $reply): void {
            $reply->update(['is_official' => false]);

            // If thread was resolved by this reply, reopen the thread
            ForumThread::where('id', $reply->thread_id)
                ->where('resolved_reply_id', $reply->id)
                ->update([
                    'status'             => ForumThread::STATUS_OPEN,
                    'resolved_reply_id'  => null,
                ]);

            ForumLog::record(
                $actor->id,
                ForumLog::ACTION_REPLY_UNOFFICIA,
                $reply->thread_id,
                $reply->id
            );
        });

        Cache::forget('forum.pinned_threads');
    }

    // ── Flagging ──────────────────────────────────────────────────────────────

    /**
     * Flag a reply for moderation review.
     * Duplicate flags from the same user throw a domain exception (DB unique constraint).
     */
    public function flagReply(User $reporter, ForumReply $reply, string $reason): ForumFlag
    {
        try {
            $flag = DB::transaction(function () use ($reporter, $reply, $reason): ForumFlag {
                if (! $reply->thread_id) {
                    throw new \DomainException('Cannot flag a reply without a valid thread reference.');
                }

                $flag = ForumFlag::create([
                    'reply_id'   => $reply->id,
                    'thread_id'  => $reply->thread_id,
                    'flagged_by' => $reporter->id,
                    'reason'     => $reason,
                    'status'     => ForumFlag::STATUS_PENDING,
                ]);

                // Mark reply as flagged
                $reply->update(['status' => ForumReply::STATUS_FLAGGED]);

                ForumLog::record($reporter->id, ForumLog::ACTION_REPLY_FLAG, $reply->thread_id, $reply->id);

                return $flag;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw new \DomainException('You have already flagged this reply.');
        }

        // Notify the reply owner that their reply was flagged (mail, queued)
        try {
            $reply->loadMissing('user');
            if ($reply->user) {
                $reply->user->notify(new \App\Notifications\Forum\ReplyFlaggedNotification($reply, $reporter));
            }
        } catch (\Throwable $e) {
            Log::warning('forum.notify.reply_flagged failed', ['error' => $e->getMessage()]);
        }

        return $flag;
    }

    /**
     * Flag a thread for moderation review.
     */
    public function flagThread(User $reporter, ForumThread $thread, string $reason): ForumFlag
    {
        try {
            $flag = DB::transaction(function () use ($reporter, $thread, $reason): ForumFlag {
                $flag = ForumFlag::create([
                    'reply_id'   => null,
                    'thread_id'  => $thread->id,
                    'flagged_by' => $reporter->id,
                    'reason'     => $reason,
                    'status'     => ForumFlag::STATUS_PENDING,
                ]);

                ForumLog::record($reporter->id, ForumLog::ACTION_THREAD_FLAG, $thread->id, null);

                return $flag;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            throw new \DomainException('You have already flagged this thread.');
        }

        return $flag;
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Strip dangerous HTML while preserving safe formatting tags.
     *
     * Uses HTMLPurifier when available (recommended: composer require ezyang/htmlpurifier).
     * Falls back to strip_tags with a safe allow-list otherwise.
     */
    private function sanitize(string $html): string
    {
        $allowed = '<p><br><b><strong><i><em><ul><ol><li><blockquote><code><pre><a>';

        if (class_exists(\HTMLPurifier::class)) {
            $config   = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.Allowed', 'p,br,b,strong,i,em,ul,ol,li,blockquote,code,pre,a[href|title]');
            $config->set('HTML.MaxImgLength', 0);   // no img tags
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
            $config->set('AutoFormat.RemoveEmpty', true);
            $purifier = new \HTMLPurifier($config);
            return $purifier->purify($html);
        }

        // Fallback — no attributes on any tag (safe but lossy)
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = strip_tags($html, $allowed);

        // Remove all attributes from allowed tags (e.g., onmouseover, style, href).
        return preg_replace('/<(\/?)([a-z0-9]+)(?:\s[^>]*)?>/i', '<$1$2>', $html) ?? $html;
    }

    /**
     * Internal: clear the official answer FK on a thread.
     */
    private function clearOfficialAnswer(int $threadId, int $replyId): void
    {
        ForumThread::where('id', $threadId)
            ->where('resolved_reply_id', $replyId)
            ->update([
                'resolved_reply_id' => null,
                'status'            => ForumThread::STATUS_OPEN,
            ]);
    }
}
