<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Api\V1\ActivityApiService;
use Illuminate\Http\Request;

class ActivityController extends ApiController
{
    public function __construct(private readonly ActivityApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'action' => ['nullable', 'string', 'max:100'],
            'entity_type' => ['nullable', 'string', 'max:100'],
            'actor_role' => ['nullable', 'string', 'max:32'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->list($filters, (int) ($filters['limit'] ?? 25));

        return $this->paginated($paginator, $paginator->items());
    }
}
