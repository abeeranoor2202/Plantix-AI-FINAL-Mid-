<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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

        return (new MailMessage())
            ->subject("Order #{$order->id} Confirmed — Plantix AI")
            ->greeting("Hello {$notifiable->name},")
            ->line("Thank you for your order! We've received **Order #{$order->id}**.")
            ->line('**Order Summary**')
            ->line("Total: " . config('plantix.currency_symbol') . number_format($order->grand_total, 2))
            ->action('View Order', route('order.details', $order->id))
            ->line('We will notify you as your order progresses.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'order_placed',
            'order_id'   => $this->order->id,
            'message'    => "Your order #{$this->order->id} has been placed successfully.",
            'total'      => $this->order->grand_total,
        ];
    }
}
