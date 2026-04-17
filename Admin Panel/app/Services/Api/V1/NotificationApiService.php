<?php

namespace App\Services\Api\V1;

use App\Models\User;
use App\Services\Notifications\NotificationCenterService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationApiService
{
    public function __construct(private readonly NotificationCenterService $service) {}

    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        if ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $expert = $actor->expert;
            if (! $expert) {
                abort(404, 'Expert profile not found.');
            }

            return $this->service->listForExpert($expert, $filters, $limit);
        }

        return $this->service->listForUser($actor, $filters, $limit);
    }

    public function unreadCount(User $actor): int
    {
        if ($actor->role === 'expert' || $actor->role === 'agency_expert') {
            $expert = $actor->expert;
            if (! $expert) {
                return 0;
            }

            return $this->service->unreadCountForExpert($expert);
        }

        return $this->service->unreadCount($actor);
    }
}
