<?php

namespace App\Policies;

use App\Models\ForumThread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ForumThreadPolicy
 *
 * Enforces the full role/permission matrix for forum threads.
 * Controllers must call $this->authorize(...) — NO role checks inside controllers.
 *
 * Matrix:
 *  Action         | farmer | expert | vendor | admin
 *  create         |   ✅   |   ✅   |   ❌   |   ✅
 *  view           |   ✅   |   ✅   |   ✅   |   ✅
 *  update (own)   |   ✅   |   ✅   |   ❌   |   ✅
 *  delete (own)   |   ✅   |   ✅   |   ❌   |   ✅
 *  delete (any)   |   ❌   |   ❌   |   ❌   |   ✅
 *  lock           |   ❌   |   ❌   |   ❌   |   ✅
 *  pin            |   ❌   |   ❌   |   ❌   |   ✅
 *  resolve        |   ❌   |   ❌   |   ❌   |   ✅
 *  archive        |   ❌   |   ❌   |   ❌   |   ✅
 *  approve        |   ❌   |   ❌   |   ❌   |   ✅
 *  flag           |   ✅   |   ✅   |   ✅   |   ✅
 */
class ForumThreadPolicy
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

        return null; // Let the individual methods decide
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    /**
     * Vendors cannot create threads.
     * Banned users cannot create threads.
     */
    public function create(User $user): bool
    {
        return $user->canCreateForumThread()
            && ! $user->isCurrentlyBanned();
    }

    /**
     * Anyone (including guests) can view an approved, non-soft-deleted thread.
     * Pending (not approved) threads are only visible to their owner.
     */
    public function view(?User $user, ForumThread $thread): bool
    {
        if ($thread->is_approved) {
            return true;           // guests and all logged-in users can read
        }

        // Unapproved: owner only
        return $user !== null && $user->id === $thread->user_id;
    }

    /**
     * Owner can update their own thread only when it's open and they are not banned.
     * Vendors cannot own threads (enforced at create), so this is safe.
     */
    public function update(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id
            && $thread->isOpen()
            && ! $user->isCurrentlyBanned();
    }

    /**
     * Owner can soft-delete their own thread when it's open.
     * Admin deleteAny is handled by 'before'.
     */
    public function delete(User $user, ForumThread $thread): bool
    {
        return $user->id === $thread->user_id
            && $thread->isOpen()
            && ! $user->isCurrentlyBanned();
    }

    /** Admin only — handled by 'before'. */
    public function lock(User $user, ForumThread $thread): bool
    {
        return false;
    }

    /** Admin only — handled by 'before'. */
    public function pin(User $user, ForumThread $thread): bool
    {
        return false;
    }

    /** Admin only — handled by 'before'. */
    public function resolve(User $user, ForumThread $thread): bool
    {
        return false;
    }

    /** Admin only — handled by 'before'. */
    public function archive(User $user, ForumThread $thread): bool
    {
        return false;
    }

    /** Admin only — handled by 'before'. */
    public function approve(User $user, ForumThread $thread): bool
    {
        return false;
    }

    /**
     * Any authenticated, non-banned user can report a thread they do not own.
     */
    public function flag(User $user, ForumThread $thread): bool
    {
        return $user->id !== $thread->user_id
            && ! $user->isCurrentlyBanned();
    }
}
