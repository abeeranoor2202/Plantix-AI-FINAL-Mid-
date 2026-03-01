<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order    = $this->order;
        $currency = config('plantix.currency_symbol', '$');

        return (new MailMessage())
            ->subject("Payment Confirmed — Order #{$order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your payment of **{$currency}" . number_format($order->grand_total, 2) . "** has been received successfully.")
            ->line("**Order:** #{$order->order_number}")
            ->line("**Date:** " . $order->created_at->format('d M Y, H:i'))
            ->action('View Your Order', route('order.details', $order->id))
            ->line('Thank you for shopping with Plantix AI!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'payment_success',
            'order_id' => $this->order->id,
            'message'  => "Payment confirmed for order #{$this->order->order_number}.",
            'total'    => $this->order->grand_total,
        ];
    }
}
