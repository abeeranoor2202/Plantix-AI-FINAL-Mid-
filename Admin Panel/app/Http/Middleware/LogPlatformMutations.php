<?php

namespace App\Http\Middleware;

use App\Models\PlatformActivity;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class LogPlatformMutations
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->attributes->get('platform_mutation_logged') === true) {
            return $response;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $user = $this->resolveUser();
        if (! $user) {
            return $response;
        }

        $route = $request->route();
        $routeName = (string) ($route?->getName() ?? 'unknown.mutation');
        $entityType = $this->entityTypeFromRouteName($routeName);

        PlatformActivity::query()->create([
            'actor_user_id' => $user->id,
            'actor_role' => $user->role ?? 'user',
            'action' => $routeName,
            'entity_type' => $entityType,
            'entity_id' => $this->resolveEntityId($request),
            'context' => [
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ],
        ]);

        $request->attributes->set('platform_mutation_logged', true);

        return $response;
    }

    private function resolveUser(): ?User
    {
        foreach (['admin', 'vendor', 'expert', 'web'] as $guard) {
            $user = auth($guard)->user();
            if ($user instanceof User) {
                return $user;
            }
        }

        return null;
    }

    private function entityTypeFromRouteName(string $routeName): string
    {
        $parts = explode('.', $routeName);

        if (count($parts) < 2) {
            return 'system';
        }

        return (string) ($parts[count($parts) - 2] ?: 'system');
    }

    private function resolveEntityId(Request $request): ?int
    {
        foreach (['id', 'order', 'appointment', 'notification', 'thread', 'reply', 'user', 'vendor', 'expert', 'product'] as $key) {
            $value = $request->route($key);
            if (is_numeric($value)) {
                return (int) $value;
            }

            if (is_object($value) && isset($value->id) && is_numeric($value->id)) {
                return (int) $value->id;
            }
        }

        return null;
    }
}
