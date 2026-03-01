<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Order;
use Illuminate\Mail\Mailables\Content;

class OrderMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Order $order,
        public readonly string $type = 'placed',  // placed | status_update
        public readonly ?string $notes = null,
    ) {
        parent::__construct();
    }

    protected function resolveSubject(): string
    {
        return match ($this->order->status) {
            'pending', 'confirmed'  => "Order Confirmed — #{$this->order->order_number}",
            'shipped'               => "Your Order is on the Way — #{$this->order->order_number}",
            'delivered', 'completed'=> "Order Delivered — #{$this->order->order_number}",
            'cancelled'             => "Order Cancelled — #{$this->order->order_number}",
            'refunded'              => "Refund Processed — #{$this->order->order_number}",
            'return_requested'      => "Return Request Received — #{$this->order->order_number}",
            default                 => "Order Update — #{$this->order->order_number}",
        };
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.order',
            with: [
                'order'         => $this->order,
                'notes'         => $this->notes,
                'recipientEmail'=> $this->order->user?->email ?? '',
            ]
        );
    }
}
