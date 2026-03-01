<?php

namespace App\Mail\User;

use App\Mail\PlantixBaseMail;
use App\Models\Order;
use Illuminate\Mail\Mailables\Content;

class PaymentMail extends PlantixBaseMail
{
    public function __construct(
        public readonly Order  $order,
        public readonly string $status,           // success | failed
        public readonly float  $amount,
        public readonly ?string $transactionId  = null,
        public readonly ?string $failureReason  = null,
    ) {
        parent::__construct();
        // Payment alerts go to a high-priority queue
        $this->onQueue('emails-critical');
    }

    protected function resolveSubject(): string
    {
        return $this->status === 'success'
            ? "Payment Confirmed — Order #{$this->order->order_number}"
            : "Payment Failed — Order #{$this->order->order_number}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user.payment',
            with: [
                'order'         => $this->order,
                'status'        => $this->status,
                'amount'        => $this->amount,
                'transactionId' => $this->transactionId,
                'failureReason' => $this->failureReason,
                'recipientEmail'=> $this->order->user?->email ?? '',
            ]
        );
    }
}
