<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Product $product,
        public readonly int     $currentQuantity
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Low Stock Alert: {$this->product->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following product is running **low on stock**.")
            ->line("**Product:** {$this->product->name}")
            ->line("**SKU:** " . ($this->product->sku ?? 'N/A'))
            ->line("**Current Stock:** {$this->currentQuantity} units")
            ->line("**Threshold:** " . config('plantix.low_stock_threshold') . ' units')
            ->action('Restock Now', route('admin.products.edit', $this->product->id))
            ->line('Please replenish this product to avoid stockouts.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'low_stock_alert',
            'product_id'       => $this->product->id,
            'product_name'     => $this->product->name,
            'current_quantity' => $this->currentQuantity,
            'message'          => "Low stock alert: {$this->product->name} has {$this->currentQuantity} units remaining.",
        ];
    }
}
