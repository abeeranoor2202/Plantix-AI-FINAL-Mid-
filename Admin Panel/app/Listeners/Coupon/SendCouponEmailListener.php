<?php

namespace App\Listeners\Coupon;

use App\Events\Coupon\CouponAssigned;
use App\Mail\User\CouponMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCouponEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handle(CouponAssigned $event): void
    {
        $user   = $event->user;
        $coupon = $event->coupon;
        $type   = $event->type; // 'assigned' or 'expiring'

        if ($user->email) {
            $this->notifLog->send(
                mailable:         new CouponMail($user, $coupon, $type),
                to:               $user->email,
                recipientName:    $user->name,
                recipientRole:    'user',
                notificationType: "coupon_{$type}",
                notifiable:       $coupon,
                userId:           $user->id,
                dedupKey:         "coupon_{$type}:{$coupon->id}:{$user->id}",
            );
        }
    }
}
