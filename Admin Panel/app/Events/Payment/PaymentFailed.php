<?php

namespace App\Events\Payment;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order   $order,
        public readonly float   $amount,
        public readonly string  $failureReason,
        public readonly ?string $transactionId = null,
    ) {}
}
