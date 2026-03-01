<?php

namespace App\Events\Expert;

use App\Models\Expert;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired when a new expert completes signup (pending admin review). */
class ExpertRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Expert $expert,
        public readonly User   $user,
    ) {}
}
