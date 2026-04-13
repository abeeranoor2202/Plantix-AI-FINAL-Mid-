<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ReturnRequest $return,
        public readonly string        $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = ucwords(str_replace('_', ' ', $this->newStatus));

        $message = match ($this->newStatus) {
            'approved'  => "Your return request for Order #{$this->return->order_id} has been **approved**. A refund will be processed shortly.",
            'rejected'  => "Your return request for Order #{$this->return->order_id} has been **rejected**. Please contact support if you have questions.",
            'refunded'  => "Your refund for Order #{$this->return->order_id} has been **processed** and will be disbursed via the selected refund method.",
            default     => "Your return request status has been updated to **{$label}**.",
        };

        return (new MailMessage())
            ->subject("Return Request Update — Order #{$this->return->order_id}")
            ->greeting("Hello {$notifiable->name},")
            ->line($message)
            ->action('View Orders', route('orders'))
            ->line('Thank you for shopping with Plantix AI.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'return_status_changed',
            'return_id' => $this->return->id,
            'order_id'  => $this->return->order_id,
            'status'    => $this->newStatus,
            'message'   => "Your return request for Order #{$this->return->order_id} is now {$this->newStatus}.",
        ];
    }
}
