<?php

namespace App\Policies;

use App\Models\ExpertApplication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ExpertApplicationPolicy
 *
 * Guards around who can submit and manage expert applications.
 */
class ExpertApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * A user can apply only when:
     *  - They are NOT already an expert
     *  - They do NOT have an active (pending/under_review) application
     */
    public function apply(User $user): bool
    {
        if ($user->role === 'expert') {
            return false;
        }

        return ! ExpertApplication::where('user_id', $user->id)
            ->whereIn('status', [
                ExpertApplication::STATUS_PENDING,
                ExpertApplication::STATUS_UNDER_REVIEW,
            ])
            ->exists();
    }

    /**
     * A user can view only their own application.
     */
    public function view(User $user, ExpertApplication $application): bool
    {
        return (int) $user->id === (int) $application->user_id;
    }

    /**
     * Admins can review any application.
     * (Intended to be used after 'admin' role check in controller/middleware.)
     */
    public function review(User $user, ExpertApplication $application): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admins can approve any pending/under_review application.
     */
    public function approve(User $user, ExpertApplication $application): bool
    {
        return $user->role === 'admin'
            && in_array($application->status, [
                ExpertApplication::STATUS_PENDING,
                ExpertApplication::STATUS_UNDER_REVIEW,
            ], true);
    }

    /**
     * Admins can reject any pending/under_review application.
     */
    public function reject(User $user, ExpertApplication $application): bool
    {
        return $user->role === 'admin'
            && in_array($application->status, [
                ExpertApplication::STATUS_PENDING,
                ExpertApplication::STATUS_UNDER_REVIEW,
            ], true);
    }
}
