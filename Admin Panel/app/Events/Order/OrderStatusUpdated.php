<?php

namespace App\Events\Order;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired whenever an order's status changes (shipped, delivered, cancelled, etc.) */
class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order  $order,
        public readonly string $previousStatus,
        public readonly string $newStatus,
        public readonly ?string $notes = null,
    ) {}
}
