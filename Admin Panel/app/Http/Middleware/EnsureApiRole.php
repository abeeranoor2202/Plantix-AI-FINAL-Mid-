<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ], 401);
        }

        $normalizedRole = $user->role === 'agency_expert' ? 'expert' : $user->role;

        if (! in_array($normalizedRole, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'data' => null,
                'errors' => ['role' => ['This role cannot access this endpoint.']],
            ], 403);
        }

        if (! $user->active) {
            return response()->json([
                'success' => false,
                'message' => 'Account disabled.',
                'data' => null,
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
