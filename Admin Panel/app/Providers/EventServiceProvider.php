<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [

        // ── [PRESERVED] Expert panel real-time / existing listeners ──────────
        \App\Events\Expert\AppointmentStatusChanged::class => [
            \App\Listeners\Expert\SendAppointmentStatusNotification::class,
        ],
        \App\Events\Expert\ExpertMentionedInForum::class => [
            \App\Listeners\Expert\SendForumMentionNotification::class,
        ],
        \App\Events\Expert\ExpertPayoutStatusChanged::class => [
            \App\Listeners\Expert\SendExpertPayoutNotification::class,
        ],

        // ── User ─────────────────────────────────────────────────────────────
        \App\Events\User\UserRegistered::class => [
            \App\Listeners\User\SendWelcomeEmailListener::class,
        ],

        // ── Orders ───────────────────────────────────────────────────────────
        \App\Events\Order\OrderPlaced::class => [
            \App\Listeners\Order\SendOrderEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],
        \App\Events\Order\OrderStatusUpdated::class => [
            \App\Listeners\Order\SendOrderEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],

        // ── Payments ─────────────────────────────────────────────────────────
        \App\Events\Payment\PaymentSucceeded::class => [
            \App\Listeners\Payment\SendPaymentEmailListener::class,
        ],
        \App\Events\Payment\PaymentFailed::class => [
            \App\Listeners\Payment\SendPaymentEmailListener::class,
        ],

        // ── Appointments (user-facing flow) ───────────────────────────────────
        \App\Events\Appointment\AppointmentCreated::class => [
            \App\Listeners\Appointment\SendAppointmentEmailListener::class,
            \App\Listeners\Expert\SendAppointmentCreatedNotification::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],
        \App\Events\Appointment\AppointmentStatusChanged::class => [
            \App\Listeners\Appointment\SendAppointmentEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],

        // ── Forum ────────────────────────────────────────────────────────────
        \App\Events\Forum\ForumReplyCreated::class => [
            \App\Listeners\Forum\SendForumEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],
        \App\Events\Forum\OfficialAnswerMarked::class => [
            \App\Listeners\Forum\SendForumEmailListener::class,
            \App\Listeners\Expert\SendForumOfficialMarkedNotification::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],
        \App\Events\Forum\ContentFlagged::class => [
            \App\Listeners\Forum\SendForumEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],

        // ── Vendors ──────────────────────────────────────────────────────────
        \App\Events\Vendor\VendorStatusChanged::class => [
            \App\Listeners\Vendor\SendVendorStatusEmailListener::class,
        ],

        // ── Experts (new registration & status via email flow) ────────────────
        \App\Events\Expert\ExpertStatusChanged::class => [
            \App\Listeners\Expert\SendExpertEmailListener::class,
            \App\Listeners\Expert\SendExpertStatusSystemNotification::class,
        ],
        \App\Events\Expert\ExpertRegistered::class => [
            \App\Listeners\Expert\SendExpertEmailListener::class,
        ],

        // ── Reviews ──────────────────────────────────────────────────────────
        \App\Events\Review\ReviewCreated::class => [
            \App\Listeners\Review\SendReviewEmailListener::class,
            \App\Listeners\Platform\ApplyPlatformImpactAndLog::class,
        ],

        // ── Coupons ──────────────────────────────────────────────────────────
        \App\Events\Coupon\CouponAssigned::class => [
            \App\Listeners\Coupon\SendCouponEmailListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}

