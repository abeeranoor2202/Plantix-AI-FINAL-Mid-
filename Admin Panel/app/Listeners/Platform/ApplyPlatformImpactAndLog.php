<?php

namespace App\Listeners\Platform;

use App\Services\Notifications\NotificationCenterService;
use App\Services\Platform\PlatformActivityService;
use App\Services\Platform\ReputationService;

class ApplyPlatformImpactAndLog
{
    public function __construct(
        private readonly ReputationService $reputation,
        private readonly PlatformActivityService $activity,
        private readonly NotificationCenterService $notifications,
    ) {}

    public function handle(object $event): void
    {
        $this->reputation->applyFromEvent($event);
        $this->activity->logFromEvent($event);
        $this->notifications->syncFromEvent($event);
    }
}
