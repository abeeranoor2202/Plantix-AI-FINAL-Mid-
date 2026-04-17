<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderDisputeSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $reason,
        public readonly string $actionUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_dispute_submitted',
            'title' => 'Order dispute submitted',
            'message' => "Order #{$this->order->order_number} has a new dispute.",
            'reason' => $this->reason,
            'order_id' => $this->order->id,
            'action_url' => $this->actionUrl,
        ];
    }
}
