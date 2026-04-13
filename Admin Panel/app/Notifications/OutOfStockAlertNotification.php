<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OutOfStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Out of Stock: {$this->product->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A product has reached zero available stock.')
            ->line("Product: {$this->product->name}")
            ->line('Current stock: 0')
            ->action('Review Product', route('admin.products.edit', $this->product->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'out_of_stock_alert',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'message' => "Out of stock: {$this->product->name} is now unavailable.",
        ];
    }
}
