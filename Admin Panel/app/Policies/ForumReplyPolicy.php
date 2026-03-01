<?php

namespace App\Policies;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ForumReplyPolicy
 *
 * Enforces the full role/permission matrix for forum replies.
 *
 * Matrix:
 *  Action          | farmer | expert | vendor | admin
 *  create          |   ✅   |   ✅   |   ✅   |   ✅   (all roles can reply)
 *  update (own)    |   ✅   |   ✅   |   ✅   |   ✅   (within edit window)
 *  delete (own)    |   ✅   |   ✅   |   ✅   |   ✅
 *  delete (any)    |   ❌   |   ❌   |   ❌   |   ✅
 *  markOfficial    |   ❌   |   ✅   |   ❌   |   ✅
 *  removeOfficial  |   ❌   |   ❌   |   ❌   |   ✅
 *  flag            |   ✅   |   ✅   |   ✅   |   ✅
 */
class ForumReplyPolicy
{
    use HandlesAuthorization;

    /**
     * Admins bypass all policy checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Any authenticated, non-banned user can reply to an open thread.
     * Thread-locked state is enforced here, not just in the controller.
     */
    public function create(User $user, ForumThread $thread): bool
    {
        return ! $user->isCurrentlyBanned()
            && ! $thread->isLocked()
            && ! $thread->isArchived()
            && $thread->is_approved;
    }

    /**
     * Reply owner can edit within the edit window.
     * Banned users cannot edit even within the window.
     */
    public function update(User $user, ForumReply $reply): bool
    {
        return $user->id === $reply->user_id
            && $reply->isEditable()
            && ! $user->isCurrentlyBanned()
            && $reply->status === ForumReply::STATUS_VISIBLE;
    }

    /**
     * Reply owner can soft-delete their own reply.
     * Admin can delete any reply (via 'before').
     */
    public function delete(User $user, ForumReply $reply): bool
    {
        return $user->id === $reply->user_id
            && ! $user->isCurrentlyBanned();
    }

    /**
     * Only experts (approved) and admins can mark a reply as official.
     * Experts can only mark replies on threads they did NOT author
     * (prevent self-marking own thread as resolved by themselves).
     */
    public function markOfficial(User $user, ForumReply $reply): bool
    {
        if (! $user->isExpert()) {
            return false;
        }

        // Expert must have an active, approved account
        $expert = $user->expert;
        if (! $expert || ! $expert->isApproved()) {
            return false;
        }

        // Load thread if not loaded
        $reply->loadMissing('thread');

        // Thread must be open (not locked/archived/resolved)
        return $reply->thread
            && $reply->thread->isOpen()
            && ! $user->isCurrentlyBanned();
    }

    /**
     * Only admins can remove official status (via 'before').
     */
    public function removeOfficial(User $user, ForumReply $reply): bool
    {
        return false;
    }

    /**
     * Any authenticated, non-banned user can flag a reply once.
     * Cannot flag your own reply.
     */
    public function flag(User $user, ForumReply $reply): bool
    {
        return $user->id !== $reply->user_id
            && ! $user->isCurrentlyBanned();
    }
}
