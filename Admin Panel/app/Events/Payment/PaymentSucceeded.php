<?php

namespace App\Events\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order    $order,
        public readonly Payment  $payment,
        public readonly float    $amount,
        public readonly string   $transactionId,
    ) {}
}
