<?php

namespace App\Events\Vendor;

use App\Models\Vendor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Vendor  $vendor,
        public readonly string  $status,
        public readonly ?string $reason = null,
    ) {}
}
