<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage())
            ->subject("Your Order Has Shipped — #{$order->order_number}")
            ->greeting("Great news, {$notifiable->name}!")
            ->line("**Order #{$order->order_number}** is on its way to you.");

        if ($order->tracking_number) {
            $mail->line("**Tracking Number:** {$order->tracking_number}");
        }

        if ($order->shipping_carrier) {
            $mail->line("**Carrier:** {$order->shipping_carrier}");
        }

        return $mail
            ->action('View Order Details', route('order.details', $order->id))
            ->line('Estimated delivery is within 3–5 business days. Thank you for your patience!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'order_shipped',
            'order_id'        => $this->order->id,
            'message'         => "Order #{$this->order->order_number} has been shipped.",
            'tracking_number' => $this->order->tracking_number,
        ];
    }
}
