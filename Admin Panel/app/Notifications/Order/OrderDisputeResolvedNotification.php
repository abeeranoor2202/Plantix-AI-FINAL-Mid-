<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Order Dispute Resolved — #' . $this->order->order_number)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('The dispute for Order #' . $this->order->order_number . ' has been resolved.')
            ->line('Final status: ' . $this->status)
            ->line('Resolution: ' . $this->resolution)
            ->action('View Resolution', $this->actionUrl)
            ->line('Thank you for using Plantix AI.');
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
