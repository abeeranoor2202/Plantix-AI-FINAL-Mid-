<?php

namespace App\Listeners\Review;

use App\Events\Review\ReviewCreated;
use App\Mail\Vendor\ReviewPostedMail;
use App\Services\NotificationLogService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendReviewEmailListener implements ShouldQueue
{
    public string $queue = 'listeners';

    public function __construct(private readonly NotificationLogService $notifLog) {}

    public function handle(ReviewCreated $event): void
    {
        $vendor  = $event->vendor->load('user');
        $review  = $event->review;
        $product = $event->product;

        if ($vendor->user?->email) {
            $this->notifLog->send(
                mailable:         new ReviewPostedMail($review, $product, $vendor),
                to:               $vendor->user->email,
                recipientName:    $vendor->user->name,
                recipientRole:    'vendor',
                notificationType: 'review_posted',
                notifiable:       $review,
                dedupKey:         "review_posted:{$review->id}",
            );
        }
    }
}
