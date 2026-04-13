<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order        = $this->order;
        $returnWindow = config('plantix.return_window_days', 7);
        $returnDeadline = now()->addDays($returnWindow)->format('d M Y');

        return (new MailMessage())
            ->subject("Order Delivered — #{$order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("**Order #{$order->order_number}** has been delivered. We hope you love your purchase!")
            ->line("Happy with your order? Leave a review to help other farmers.")
            ->action('Review Your Order', route('order.details', $order->id))
            ->line("Not satisfied? You have until **{$returnDeadline}** to initiate a return.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'order_delivered',
            'order_id' => $this->order->id,
            'message'  => "Order #{$this->order->order_number} has been delivered.",
        ];
    }
}
