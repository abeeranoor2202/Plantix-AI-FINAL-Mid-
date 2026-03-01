<?php

namespace App\Events\Expert;

use App\Models\Expert;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpertStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Expert  $expert,
        public readonly string  $status,
        public readonly ?string $reason = null,
    ) {}
}
