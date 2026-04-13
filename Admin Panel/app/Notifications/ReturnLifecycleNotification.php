<?php

namespace App\Notifications;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReturnLifecycleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly ReturnRequest $return,
        public readonly string $event,
        public readonly ?string $message = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->event) {
            'requested'         => 'Return Request Received',
            'approved'          => 'Return Request Approved',
            'rejected'          => 'Return Request Rejected',
            'refund_processing' => 'Refund Processing Started',
            'completed'         => 'Return Completed',
            default             => 'Return Update',
        };

        $body = match ($this->event) {
            'requested'         => "A return request was submitted for Order #{$this->return->order_id}.",
            'approved'          => "Your return request for Order #{$this->return->order_id} has been approved.",
            'rejected'          => "Your return request for Order #{$this->return->order_id} has been rejected.",
            'refund_processing' => "Refund processing has started for Order #{$this->return->order_id}.",
            'completed'         => "Refund completion has been recorded for Order #{$this->return->order_id}.",
            default             => "Your return request for Order #{$this->return->order_id} has been updated.",
        };

        if ($this->message) {
            $body .= ' ' . $this->message;
        }

        return (new MailMessage())
            ->subject($subject . ' — Order #' . $this->return->order_id)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line($body)
            ->action('View Orders', route('orders'))
            ->line('Thank you for shopping with Plantix AI.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'return_lifecycle',
            'event'     => $this->event,
            'return_id' => $this->return->id,
            'order_id'  => $this->return->order_id,
            'status'    => $this->return->status,
            'message'   => $this->message ?: 'Return status updated.',
        ];
    }
}