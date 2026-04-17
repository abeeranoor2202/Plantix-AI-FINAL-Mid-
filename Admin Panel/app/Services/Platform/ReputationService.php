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
use App\Models\Expert;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    public function applyFromEvent(object $event): void
    {
        match (true) {
            $event instanceof ForumReplyCreated => $this->applyForumReplyCreated($event),
            $event instanceof OfficialAnswerMarked => $this->applyOfficialAnswerMarked($event),
            $event instanceof ContentFlagged => $this->applyContentFlagged($event),
            $event instanceof OrderPlaced => $this->applyOrderPlaced($event),
            $event instanceof OrderStatusUpdated => $this->applyOrderStatusUpdated($event),
            $event instanceof ReviewCreated => $this->applyReviewCreated($event),
            $event instanceof AppointmentCreated => $this->applyAppointmentCreated($event),
            $event instanceof AppointmentStatusChanged => $this->applyAppointmentStatusChanged($event),
            default => null,
        };
    }

    private function applyForumReplyCreated(ForumReplyCreated $event): void
    {
        $reply = $event->reply;

        if ($reply->is_expert_reply && $reply->expert_id) {
            $expert = Expert::find($reply->expert_id);
            if ($expert) {
                $this->adjustExpert($expert, 2);
            }
        } elseif ($reply->user_id) {
            $user = User::find($reply->user_id);
            if ($user) {
                $this->adjustUser($user, 2);
            }
        }
    }

    private function applyOfficialAnswerMarked(OfficialAnswerMarked $event): void
    {
        $reply = $event->reply;

        if ($reply->expert_id) {
            $expert = Expert::find($reply->expert_id);
            if ($expert) {
                $this->adjustExpert($expert, 6);
            }
        }

        if ($event->thread->user_id) {
            $threadAuthor = User::find($event->thread->user_id);
            if ($threadAuthor) {
                $this->adjustUser($threadAuthor, 1);
            }
        }
    }

    private function applyContentFlagged(ContentFlagged $event): void
    {
        $flag = $event->flag;

        if ($flag->flagged_by) {
            $reporter = User::find($flag->flagged_by);
            if ($reporter) {
                $this->adjustUser($reporter, 1);
            }
        }

        $targetUser = $flag->reply?->user_id
            ? User::find($flag->reply->user_id)
            : ($flag->thread?->user_id ? User::find($flag->thread->user_id) : null);

        if ($targetUser) {
            $this->adjustUser($targetUser, -3);
        }
    }

    private function applyOrderPlaced(OrderPlaced $event): void
    {
        $order = $event->order;

        if ($order->user_id) {
            $buyer = User::find($order->user_id);
            if ($buyer) {
                $this->adjustUser($buyer, 3);
            }
        }

        if ($order->vendor_id) {
            $vendor = Vendor::find($order->vendor_id);
            if ($vendor) {
                $this->adjustVendor($vendor, 4);
            }
        }
    }

    private function applyOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        $order = $event->order;
        $status = $event->newStatus;

        if (in_array($status, ['delivered', 'completed'], true)) {
            if ($order->vendor_id) {
                $vendor = Vendor::find($order->vendor_id);
                if ($vendor) {
                    $this->adjustVendor($vendor, 3);
                }
            }
            if ($order->user_id) {
                $buyer = User::find($order->user_id);
                if ($buyer) {
                    $this->adjustUser($buyer, 1);
                }
            }
        }

        if (in_array($status, ['cancelled', 'rejected', 'refunded', 'returned'], true) && $order->vendor_id) {
            $vendor = Vendor::find($order->vendor_id);
            if ($vendor) {
                $this->adjustVendor($vendor, -4);
            }
        }
    }

    private function applyReviewCreated(ReviewCreated $event): void
    {
        $rating = (int) $event->review->rating;

        if ($event->review->user_id) {
            $reviewer = User::find($event->review->user_id);
            if ($reviewer) {
                $this->adjustUser($reviewer, 1);
            }
        }

        $delta = $rating >= 4 ? 3 : ($rating <= 2 ? -4 : 1);
        $this->adjustVendor($event->vendor, $delta);
    }

    private function applyAppointmentCreated(AppointmentCreated $event): void
    {
        $appointment = $event->appointment;

        if ($appointment->user_id) {
            $user = User::find($appointment->user_id);
            if ($user) {
                $this->adjustUser($user, 1);
            }
        }

        if ($appointment->expert_id) {
            $expert = Expert::find($appointment->expert_id);
            if ($expert) {
                $this->adjustExpert($expert, 1);
            }
        }
    }

    private function applyAppointmentStatusChanged(AppointmentStatusChanged $event): void
    {
        $appointment = $event->appointment;
        $status = $event->newStatus;

        if ($appointment->expert_id) {
            $expert = Expert::find($appointment->expert_id);
            if ($expert) {
                if ($status === 'completed') {
                    $this->adjustExpert($expert, 4);
                }
                if (in_array($status, ['rejected', 'cancelled'], true)) {
                    $this->adjustExpert($expert, -3);
                }
            }
        }

        if ($appointment->user_id) {
            $user = User::find($appointment->user_id);
            if ($user && $status === 'completed') {
                $this->adjustUser($user, 1);
            }
        }
    }

    public function adjustUser(User $user, int $delta): void
    {
        DB::transaction(function () use ($user, $delta) {
            $user->refresh();
            $newScore = max(0, (int) $user->reputation_score + $delta);

            $user->forceFill([
                'reputation_score' => $newScore,
                'reputation_level' => $this->levelFor($newScore),
            ])->save();
        });
    }

    public function adjustExpert(Expert $expert, int $delta): void
    {
        DB::transaction(function () use ($expert, $delta) {
            $expert->refresh();
            $newScore = max(0, (int) $expert->reputation_score + $delta);

            $expert->forceFill([
                'reputation_score' => $newScore,
                'reputation_level' => $this->levelFor($newScore),
            ]);

            if ($newScore < 10 && method_exists($expert, 'isApproved') && $expert->isApproved()) {
                $expert->status = Expert::STATUS_UNDER_REVIEW;
            }

            $expert->save();
        });
    }

    public function adjustVendor(Vendor $vendor, int $delta): void
    {
        DB::transaction(function () use ($vendor, $delta) {
            $vendor->refresh();
            $newScore = max(0, (int) $vendor->reputation_score + $delta);

            $vendor->forceFill([
                'reputation_score' => $newScore,
                'reputation_level' => $this->levelFor($newScore),
            ]);

            if ($newScore < 10 && ! $vendor->isSuspended()) {
                $vendor->setLifecycleStatus(Vendor::STATUS_SUSPENDED);
            }

            $vendor->save();
        });
    }

    public function levelFor(int $score): string
    {
        return match (true) {
            $score >= 120 => 'elite',
            $score >= 80 => 'trusted',
            $score >= 40 => 'rising',
            $score >= 10 => 'active',
            default => 'neutral',
        };
    }
}
