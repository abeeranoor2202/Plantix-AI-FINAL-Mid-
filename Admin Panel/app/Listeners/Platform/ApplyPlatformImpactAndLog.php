<?php

namespace App\Listeners\Platform;

use App\Services\Notifications\NotificationCenterService;
use App\Services\Platform\PlatformActivityService;

class ApplyPlatformImpactAndLog
{
    public function __construct(
        private readonly PlatformActivityService $activity,
        private readonly NotificationCenterService $notifications,
    ) {}

    public function handle(object $event): void
    {
        $this->activity->logFromEvent($event);
        $this->notifications->syncFromEvent($event);
    }
}
