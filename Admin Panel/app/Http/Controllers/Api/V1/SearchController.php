<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Response\ApiErrorCode;
use App\Services\Platform\UniversalSearchService;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function __construct(private readonly UniversalSearchService $searchService) {}

    public function index(Request $request)
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $query = trim((string) $validated['query']);

        if ($query === '') {
            return $this->businessError(ApiErrorCode::VALIDATION_FAILED, 'Query cannot be empty.', [
                'query' => ['Search query is required.'],
            ], 422);
        }

        $result = $this->searchService->search(
            $request->user(),
            $query,
            (int) ($validated['limit'] ?? 8)
        );

        return $this->ok([
            'query' => $query,
            'result' => $result,
        ], 'Search completed.');
    }
}
