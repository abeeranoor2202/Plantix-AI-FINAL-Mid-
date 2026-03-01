<?php

namespace App\Mail\Vendor;

use App\Mail\PlantixBaseMail;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Mail\Mailables\Content;

class VendorOrderMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Order  $order,
        public readonly Vendor $vendor,
        public readonly string $type = 'new',   // new | update
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        if ($this->type === 'new') {
            return "🛒 New Order #{$this->order->order_number} on {$this->vendor->title}";
        }
        return match ($this->order->status) {
            'cancelled' => "Order Cancelled — #{$this->order->order_number}",
            'refunded'  => "Refund Issued — #{$this->order->order_number}",
            default     => "Order Update — #{$this->order->order_number}",
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor.order',
            with: [
                'order'         => $this->order,
                'vendor'        => $this->vendor,
                'type'          => $this->type,
                'recipientEmail'=> $this->vendor->author?->email ?? '',
            ]
        );
    }
}
