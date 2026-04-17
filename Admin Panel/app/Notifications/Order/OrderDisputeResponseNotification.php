<?php

namespace App\Notifications\Order;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDisputeResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Order $order,
        public readonly string $response,
        public readonly string $actionUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Vendor Responded to Dispute — #' . $this->order->order_number)
            ->greeting('Hello ' . ($notifiable->name ?? 'there') . ',')
            ->line('A vendor has responded to the dispute for Order #' . $this->order->order_number . '.')
            ->line('Response: ' . $this->response)
            ->action('View Dispute', $this->actionUrl)
            ->line('Please review this response and continue the workflow if needed.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_dispute_response',
            'title' => 'Vendor responded to dispute',
            'message' => "A response was added for Order #{$this->order->order_number}.",
            'response' => $this->response,
            'order_id' => $this->order->id,
            'action_url' => $this->actionUrl,
        ];
    }
}
