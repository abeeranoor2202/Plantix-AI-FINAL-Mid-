<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AppointmentPolicy
 *
 * Authorization guards for appointment actions.
 * Super-admin bypass is handled in `before()`.
 *
 * Note: Admin guard uses a separate guard ('admin'). For API routes using
 * Sanctum ('web' / 'sanctum'), this policy applies directly.
 */
class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Super-admin bypass: any user whose role is 'admin' can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }
        if (isset($user->role) && $user->role === 'admin') {
            return true;
        }
        return null; // defer to individual methods
    }

    /** Customer can view their own. Expert can view appointments assigned to them. */
    public function view(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->user_id === (int) $user->id
            || (int) optional($appointment->expert)->user_id === (int) $user->id;
    }

    /** Customer can cancel if appointment allows it. */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->user_id === (int) $user->id
            && $appointment->canBeCancelledByCustomer();
    }

    /** Customer can request a reschedule on their confirmed appointment. */
    public function reschedule(User $user, Appointment $appointment): bool
    {
        return (int) $appointment->user_id === (int) $user->id
            && $appointment->canBeRescheduled();
    }

    /** Expert or admin can update notes / meeting link. */
    public function update(User $user, Appointment $appointment): bool
    {
        return (int) optional($appointment->expert)->user_id === (int) $user->id;
    }

    /** Only admin can issue a refund (handled at controller level with admin guard). */
    public function refund(User $user, Appointment $appointment): bool
    {
        return false; // admin bypass via `before()`
    }

    /** Only admin can reassign expert. */
    public function reassign(User $user, Appointment $appointment): bool
    {
        return false; // admin bypass via `before()`
    }

    /** Only admin can force-confirm. */
    public function forceConfirm(User $user, Appointment $appointment): bool
    {
        return false; // admin bypass via `before()`
    }
}

