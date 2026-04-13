<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification implements ShouldQueue
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
            ->subject("Payment Failed — Order #{$order->order_number}")
            ->greeting("Hello {$notifiable->name},")
            ->error()
            ->line("Unfortunately, the payment for **Order #{$order->order_number}** could not be processed.")
            ->line('This can happen due to insufficient funds, an incorrect card number, or a temporary issue with your bank.')
            ->action('Try Again', route('checkout.pay', $order->id))
            ->line('Your cart items are still saved. If you continue to experience issues, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'payment_failed',
            'order_id' => $this->order->id,
            'message'  => "Payment failed for order #{$this->order->order_number}. Please retry.",
        ];
    }
}
