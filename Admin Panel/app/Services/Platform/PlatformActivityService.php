<?php

namespace App\Services\Platform;

use App\Events\Appointment\AppointmentCreated;
use App\Events\Appointment\AppointmentStatusChanged;
use App\Events\Forum\ContentFlagged;
use App\Events\Forum\ForumReplyCreated;
use App\Events\Forum\OfficialAnswerMarked;
use App\Events\Order\OrderPlaced;
use App\Events\Order\OrderStatusUpdated;
use App\Events\Review\ReviewCreated;
use App\Models\PlatformActivity;
use App\Models\User;

class PlatformActivityService
{
    public function logFromEvent(object $event): void
    {
        match (true) {
            $event instanceof ForumReplyCreated => $this->logForumReplyCreated($event),
            $event instanceof OfficialAnswerMarked => $this->logOfficialAnswerMarked($event),
            $event instanceof ContentFlagged => $this->logContentFlagged($event),
            $event instanceof OrderPlaced => $this->logOrderPlaced($event),
            $event instanceof OrderStatusUpdated => $this->logOrderStatusUpdated($event),
            $event instanceof ReviewCreated => $this->logReviewCreated($event),
            $event instanceof AppointmentCreated => $this->logAppointmentCreated($event),
            $event instanceof AppointmentStatusChanged => $this->logAppointmentStatusChanged($event),
            default => null,
        };
    }

    public function log(
        ?int $actorUserId,
        string $action,
        ?string $entityType,
        ?int $entityId,
        array $context = [],
        ?string $actorRole = null,
    ): void {
        $resolvedRole = $actorRole;
        if ($resolvedRole === null && $actorUserId) {
            $resolvedRole = User::query()->whereKey($actorUserId)->value('role');
        }

        PlatformActivity::create([
            'actor_user_id' => $actorUserId,
            'actor_role' => $resolvedRole,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'context' => $context,
        ]);
    }

    private function logForumReplyCreated(ForumReplyCreated $event): void
    {
        $reply = $event->reply;

        $this->log(
            $reply->user_id,
            'forum.reply.created',
            'forum_thread',
            $event->thread->id,
            [
                'reply_id' => $reply->id,
                'is_expert_reply' => (bool) $reply->is_expert_reply,
            ]
        );
    }

    private function logOfficialAnswerMarked(OfficialAnswerMarked $event): void
    {
        $this->log(
            $event->reply->user_id,
            'forum.reply.marked_official',
            'forum_reply',
            $event->reply->id,
            [
                'thread_id' => $event->thread->id,
                'expert_id' => $event->reply->expert_id,
            ]
        );
    }

    private function logContentFlagged(ContentFlagged $event): void
    {
        $flag = $event->flag;

        $this->log(
            $flag->flagged_by,
            'forum.content.flagged',
            $flag->reply_id ? 'forum_reply' : 'forum_thread',
            $flag->reply_id ?: $flag->thread_id,
            [
                'flag_id' => $flag->id,
                'reason' => $flag->reason,
            ]
        );
    }

    private function logOrderPlaced(OrderPlaced $event): void
    {
        $order = $event->order;

        $this->log(
            $order->user_id,
            'order.placed',
            'order',
            $order->id,
            [
                'vendor_id' => $order->vendor_id,
                'total' => (float) $order->total,
                'status' => $order->status,
            ]
        );
    }

    private function logOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        $order = $event->order;

        $this->log(
            null,
            'order.status.updated',
            'order',
            $order->id,
            [
                'vendor_id' => $order->vendor_id,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
            ],
            'system'
        );
    }

    private function logReviewCreated(ReviewCreated $event): void
    {
        $review = $event->review;

        $this->log(
            $review->user_id,
            'review.created',
            'review',
            $review->id,
            [
                'vendor_id' => $event->vendor->id,
                'product_id' => $event->product->id,
                'rating' => (int) $review->rating,
            ]
        );
    }

    private function logAppointmentCreated(AppointmentCreated $event): void
    {
        $appointment = $event->appointment;

        $this->log(
            $appointment->user_id,
            'appointment.created',
            'appointment',
            $appointment->id,
            [
                'expert_id' => $appointment->expert_id,
                'scheduled_at' => $appointment->scheduled_at?->toDateTimeString(),
                'status' => $appointment->status,
            ]
        );
    }

    private function logAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;

        $this->log(
            null,
            'appointment.status.updated',
            'appointment',
            $appointment->id,
            [
                'expert_id' => $appointment->expert_id,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
            ],
            'system'
        );
    }
}
