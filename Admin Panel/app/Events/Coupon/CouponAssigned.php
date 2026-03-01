<?php

namespace App\Events\Coupon;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Coupon $coupon,
        public readonly User   $user,
        public readonly string $type = 'assigned',  // assigned | expiring
    ) {}
}
