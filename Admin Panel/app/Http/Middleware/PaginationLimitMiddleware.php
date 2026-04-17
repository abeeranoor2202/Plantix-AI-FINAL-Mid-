<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PaginationLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $limit = (int) $request->query('limit', $request->query('per_page', 20));

        if ($limit <= 0) {
            $limit = 20;
        }

        $limit = min($limit, 100);

        // Normalize both params so controllers/services can rely on one cap.
        $request->query->set('limit', $limit);
        $request->query->set('per_page', $limit);

        return $next($request);
    }
}
