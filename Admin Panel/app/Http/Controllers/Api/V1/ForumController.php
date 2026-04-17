<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ForumThread;
use App\Services\Api\V1\ForumApiService;
use Illuminate\Http\Request;

class ForumController extends ApiController
{
    public function __construct(private readonly ForumApiService $service) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:open,resolved,locked,archived'],
            'sort_by' => ['nullable', 'in:latest,oldest,popular'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $paginator = $this->service->list($filters, (int) ($filters['limit'] ?? 15));

        return $this->paginated($paginator, $paginator->items());
    }

    public function show(ForumThread $thread)
    {
        $thread = $this->service->detail($thread);

        return $this->ok($thread);
    }
}
