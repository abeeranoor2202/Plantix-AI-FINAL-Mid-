<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order  $order,
        public readonly string $oldStatus,
        public readonly string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        return (new MailMessage())
            ->subject("Order #{$this->order->id} Status Updated — {$label}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **Order #{$this->order->id}** status has been updated to **{$label}**.")
            ->action('Track Order', route('order.details', $this->order->id))
            ->line('Thank you for shopping with Plantix AI.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'order_status_changed',
            'order_id'   => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message'    => "Order #{$this->order->id} status changed to {$this->newStatus}.",
        ];
    }
}
