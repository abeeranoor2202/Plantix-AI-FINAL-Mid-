<?php

namespace App\Listeners\Platform;

use App\Services\Platform\PlatformActivityService;
use App\Services\Platform\ReputationService;

class ApplyPlatformImpactAndLog
{
    public function __construct(
        private readonly ReputationService $reputation,
        private readonly PlatformActivityService $activity,
    ) {}

    public function handle(object $event): void
    {
        $this->reputation->applyFromEvent($event);
        $this->activity->logFromEvent($event);
    }
}
