<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderDisputeResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $resolution,
        public readonly string $status,
        public readonly string $actionUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_dispute_resolved',
            'title' => 'Order dispute resolved',
            'message' => "Order #{$this->order->order_number} dispute has been marked {$this->status}.",
            'resolution' => $this->resolution,
            'status' => $this->status,
            'order_id' => $this->order->id,
            'action_url' => $this->actionUrl,
        ];
    }
}
