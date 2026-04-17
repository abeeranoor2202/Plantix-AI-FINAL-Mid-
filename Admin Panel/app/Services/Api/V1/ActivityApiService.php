<?php

namespace App\Services\Api\V1;

use App\Models\PlatformActivity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityApiService
{
    public function list(array $filters, int $limit): LengthAwarePaginator
    {
        $query = PlatformActivity::query()->with('actor:id,name,email');

        if (! empty($filters['action'])) {
            $query->where('action', 'like', '%' . $filters['action'] . '%');
        }

        if (! empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (! empty($filters['actor_role'])) {
            $query->where('actor_role', $filters['actor_role']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($limit);
    }
}
