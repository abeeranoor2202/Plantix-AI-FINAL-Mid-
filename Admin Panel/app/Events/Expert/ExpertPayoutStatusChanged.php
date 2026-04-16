<?php

namespace App\Events\Expert;

use App\Models\Payout;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpertPayoutStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Payout $payout,
        public readonly ?string $fromStatus,
        public readonly ?string $toStatus,
    ) {}
}
