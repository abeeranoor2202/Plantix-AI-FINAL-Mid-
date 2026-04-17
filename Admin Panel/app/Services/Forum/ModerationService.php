<?php

namespace App\Services\Forum;

use App\Models\ForumFlag;
use App\Models\ForumLog;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use App\Notifications\Forum\ThreadLockedNotification;
use App\Notifications\Forum\ThreadResolvedNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ModerationService
 *
 * All admin-level moderation actions for the forum.
 * Only called after the ForumThreadPolicy / ForumReplyPolicy 'before' admin bypass
 * has already passed in the controller. This service does not re-check
 * permissions — it assumes the caller is authorised.
 */
class ModerationService
{
    // ── Thread State Transitions ──────────────────────────────────────────────

    /**
     * Lock a thread. Notifies the thread owner by mail (queued).
     * State: open → locked (admin may also lock resolved/archived threads).
     */
    public function lockThread(User $admin, ForumThread $thread, ?string $reason = null): void
    {
        DB::transaction(function () use ($admin, $thread, $reason): void {
            $thread->update(['status' => ForumThread::STATUS_LOCKED]);
            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_THREAD_LOCK,
                $thread->id,
                null,
                ['reason' => $reason]
            );
        });

        Cache::forget('forum.pinned_threads');

        $thread->loadMissing('user');
        if ($thread->user) {
            try {
                $thread->user->notify(new ThreadLockedNotification($thread, $reason));
            } catch (\Throwable $e) {
                Log::warning('forum.notify.thread_locked failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Unlock a thread (revert locked → open).
     */
    public function unlockThread(User $admin, ForumThread $thread): void
    {
        DB::transaction(function () use ($admin, $thread): void {
            $thread->update(['status' => ForumThread::STATUS_OPEN]);
            ForumLog::record($admin->id, ForumLog::ACTION_THREAD_UNLOCK, $thread->id);
        });

        Cache::forget('forum.pinned_threads');
    }

    /**
     * Mark a thread resolved, linking it to the official reply.
     * Notifies the thread owner by mail (queued).
     * State: open → resolved.
     */
    public function resolveThread(User $admin, ForumThread $thread, ForumReply $reply): void
    {
        if ($reply->thread_id !== $thread->id) {
            throw new \DomainException('Reply does not belong to the specified thread.');
        }

        DB::transaction(function () use ($admin, $thread, $reply): void {
            $thread->update([
                'status'            => ForumThread::STATUS_RESOLVED,
                'resolved_reply_id' => $reply->id,
            ]);

            // Ensure the linked reply is marked as official answer
            ForumReply::where('thread_id', $thread->id)
                ->where('is_official', true)
                ->where('id', '!=', $reply->id)
                ->update(['is_official' => false]);

            $reply->update(['is_official' => true]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_THREAD_RESOLVE,
                $thread->id,
                $reply->id
            );
        });

        $thread->loadMissing('user');
        if ($thread->user) {
            try {
                $thread->user->notify(new ThreadResolvedNotification($thread, $reply));
            } catch (\Throwable $e) {
                Log::warning('forum.notify.thread_resolved failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Archive a thread. locked | resolved → archived.
     * Admin override also allows open → archived.
     */
    public function archiveThread(User $admin, ForumThread $thread): void
    {
        DB::transaction(function () use ($admin, $thread): void {
            $thread->update(['status' => ForumThread::STATUS_ARCHIVED]);
            ForumLog::record($admin->id, ForumLog::ACTION_THREAD_ARCHIVE, $thread->id);
        });

        Cache::forget('forum.pinned_threads');
    }

    /**
     * Restore an archived thread back to open state.
     */
    public function unarchiveThread(User $admin, ForumThread $thread): void
    {
        DB::transaction(function () use ($admin, $thread): void {
            $thread->update(['status' => ForumThread::STATUS_OPEN]);
            ForumLog::record($admin->id, ForumLog::ACTION_THREAD_UNARCHIVE, $thread->id);
        });

        Cache::forget('forum.pinned_threads');
    }

    /**
     * Pin or unpin a thread.
     */
    public function togglePin(User $admin, ForumThread $thread): bool
    {
        $newState = ! $thread->is_pinned;

        DB::transaction(function () use ($admin, $thread, $newState): void {
            $thread->update(['is_pinned' => $newState]);
            ForumLog::record(
                $admin->id,
                $newState ? ForumLog::ACTION_THREAD_PIN : ForumLog::ACTION_THREAD_UNPIN,
                $thread->id
            );
        });

        Cache::forget('forum.pinned_threads');

        return $newState;
    }

    /**
     * Approve a pending thread.
     */
    public function approveThread(User $admin, ForumThread $thread): void
    {
        DB::transaction(function () use ($admin, $thread): void {
            $thread->update(['is_approved' => true, 'status' => ForumThread::STATUS_OPEN]);
            ForumLog::record($admin->id, ForumLog::ACTION_THREAD_APPROVE, $thread->id);
        });
    }

    /**
     * Permanently delete a thread and cascade cleanup to replies, flags, and logs.
     */
    public function deleteThread(User $admin, ForumThread $thread): void
    {
        DB::transaction(function () use ($admin, $thread): void {
            ForumLog::record($admin->id, ForumLog::ACTION_THREAD_DELETE, $thread->id);
            // DB cascades remove replies and reply flags; logs keep their row but null thread_id
            $thread->forceDelete();
        });

        Cache::forget('forum.pinned_threads');
        Cache::forget('forum.category_counts');
    }

    /**
     * Permanently delete a single reply. Clears official/resolved status if needed.
     */
    public function deleteReply(User $admin, ForumReply $reply): void
    {
        DB::transaction(function () use ($admin, $reply): void {
            $thread = $reply->thread;

            // Remove direct child replies first so the thread stays visually clean.
            $reply->children()->withTrashed()->get()->each(function (ForumReply $child) use ($admin): void {
                ForumLog::record($admin->id, ForumLog::ACTION_REPLY_DELETE, $child->thread_id, $child->id);

                if ($child->is_official) {
                    ForumThread::where('id', $child->thread_id)
                        ->where('resolved_reply_id', $child->id)
                        ->update(['resolved_reply_id' => null, 'status' => ForumThread::STATUS_OPEN]);
                }

                $child->forceDelete();
                $thread?->decrementRepliesCount();
            });

            if ($reply->is_official) {
                ForumThread::where('id', $reply->thread_id)
                    ->where('resolved_reply_id', $reply->id)
                    ->update(['resolved_reply_id' => null, 'status' => ForumThread::STATUS_OPEN]);

                $reply->update(['is_official' => false]);
            }

            ForumLog::record($admin->id, ForumLog::ACTION_REPLY_DELETE, $reply->thread_id, $reply->id);
            $reply->forceDelete();
            $thread?->decrementRepliesCount();
        });
    }

    /**
     * Change a thread's category.
     */
    public function changeCategory(User $admin, ForumThread $thread, int $categoryId): void
    {
        $thread->update(['forum_category_id' => $categoryId]);
        Cache::forget('forum.category_counts');
    }

    // ── Flag Moderation ───────────────────────────────────────────────────────

    /**
     * Resolve a flag while keeping the reply visible.
     */
    public function resolveFlag(User $admin, ForumFlag $flag): void
    {
        DB::transaction(function () use ($admin, $flag): void {
            $lockedFlag = ForumFlag::query()
                ->with('reply')
                ->whereKey($flag->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedFlag->status, [ForumFlag::STATUS_PENDING], true)) {
                throw new \DomainException('This report has already been processed.');
            }

            $reply = $lockedFlag->reply;

            $lockedFlag->update([
                'status'      => ForumFlag::STATUS_RESOLVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            if ($reply) {
                ForumReply::query()->whereKey($reply->id)->lockForUpdate()->update(['status' => ForumReply::STATUS_VISIBLE]);
            }

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_FLAG_RESOLVE,
                $reply?->thread_id,
                $reply?->id,
                ['flag_id' => $lockedFlag->id, 'mode' => 'keep']
            );
        });
    }

    /**
     * Resolve a flag by deleting the reported reply.
     */
    public function resolveFlagByDeletingReply(User $admin, ForumFlag $flag): void
    {
        DB::transaction(function () use ($admin, $flag): void {
            $lockedFlag = ForumFlag::query()
                ->with('reply')
                ->whereKey($flag->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedFlag->status, [ForumFlag::STATUS_PENDING], true)) {
                throw new \DomainException('This report has already been processed.');
            }

            $reply = $lockedFlag->reply
                ? ForumReply::query()->whereKey($lockedFlag->reply->id)->lockForUpdate()->first()
                : null;

            $threadId = $reply?->thread_id;
            $deletedReplyId = $reply?->id;

            if ($reply) {
                $this->deleteReply($admin, $reply);
            }

            $lockedFlag->update([
                'status'      => ForumFlag::STATUS_RESOLVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_FLAG_RESOLVE,
                $threadId,
                null,
                ['flag_id' => $lockedFlag->id, 'mode' => 'delete_reply', 'deleted_reply_id' => $deletedReplyId]
            );
        });
    }

    /**
     * Resolve a thread-targeted flag by archiving the reported thread.
     */
    public function resolveFlagByArchivingThread(User $admin, ForumFlag $flag): void
    {
        $thread = $flag->thread;

        if (! $thread) {
            throw new \DomainException('This report is not linked to an active thread.');
        }

        DB::transaction(function () use ($admin, $flag, $thread): void {
            $lockedFlag = ForumFlag::query()->whereKey($flag->id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedFlag->status, [ForumFlag::STATUS_PENDING], true)) {
                throw new \DomainException('This report has already been processed.');
            }

            $lockedThread = ForumThread::query()->whereKey($thread->id)->lockForUpdate()->firstOrFail();

            $lockedThread->update(['status' => ForumThread::STATUS_ARCHIVED]);

            $lockedFlag->update([
                'status'      => ForumFlag::STATUS_RESOLVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_FLAG_RESOLVE,
                $lockedThread->id,
                null,
                ['flag_id' => $lockedFlag->id, 'mode' => 'archive_thread']
            );
        });

        Cache::forget('forum.pinned_threads');
        Cache::forget('forum.category_counts');
    }

    /**
     * Ignore a report without changing reply visibility/content.
     */
    public function ignoreFlag(User $admin, ForumFlag $flag): void
    {
        DB::transaction(function () use ($admin, $flag): void {
            $lockedFlag = ForumFlag::query()
                ->with('reply')
                ->whereKey($flag->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedFlag->status, [ForumFlag::STATUS_PENDING], true)) {
                throw new \DomainException('This report has already been processed.');
            }

            $reply = $lockedFlag->reply;

            $lockedFlag->update([
                'status'      => ForumFlag::STATUS_IGNORED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_FLAG_IGNORE,
                $reply?->thread_id,
                $reply?->id,
                ['flag_id' => $lockedFlag->id]
            );
        });
    }

    // ── User Banning ──────────────────────────────────────────────────────────

    /**
     * Ban a user.
     *
     * @param  \DateTimeInterface|\Carbon\Carbon|null  $until  null = permanent ban
     */
    public function banUser(
        User    $admin,
        User    $target,
        string  $reason,
        ?\DateTimeInterface $until = null,
        bool    $shadow = false
    ): void {
        if ($target->isAdmin()) {
            throw new \DomainException('Admins cannot be banned.');
        }

        DB::transaction(function () use ($admin, $target, $reason, $until, $shadow): void {
            $target->update([
                'is_banned'        => ! $shadow,
                'is_shadow_banned' => $shadow,
                'banned_until'     => $until,
                'banned_reason'    => $reason,
                'status'           => $shadow ? 'suspended' : 'banned',
                'active'           => false,
            ]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_USER_BAN,
                null,
                null,
                [
                    'target_user_id' => $target->id,
                    'shadow'         => $shadow,
                    'until'          => $until?->format('Y-m-d H:i:s'),
                    'reason'         => $reason,
                ]
            );
        });
    }

    /**
     * Unban a user.
     */
    public function unbanUser(User $admin, User $target): void
    {
        DB::transaction(function () use ($admin, $target): void {
            $target->update([
                'is_banned'        => false,
                'is_shadow_banned' => false,
                'banned_until'     => null,
                'banned_reason'    => null,
                'status'           => 'active',
                'active'           => true,
            ]);

            ForumLog::record(
                $admin->id,
                ForumLog::ACTION_USER_UNBAN,
                null,
                null,
                ['target_user_id' => $target->id]
            );
        });
    }
}
