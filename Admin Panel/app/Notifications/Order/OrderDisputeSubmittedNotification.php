<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Order Dispute Submitted — #' . $this->order->order_number)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('A dispute has been submitted for Order #' . $this->order->order_number . '.')
            ->line('Reason: ' . $this->reason)
            ->action('View Dispute', $this->actionUrl)
            ->line('Please review and take the required action.');
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
