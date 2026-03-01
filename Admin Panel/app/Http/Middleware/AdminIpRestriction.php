<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminIpRestriction
 *
 * Optional IP whitelist for the admin panel.
 * Enabled by setting ADMIN_IP_WHITELIST in .env:
 *
 *   ADMIN_IP_WHITELIST=192.168.1.100,10.0.0.0/24,203.0.113.5
 *
 * If the env is empty or not set, all IPs are allowed (no restriction).
 * Supports single IPs and CIDR ranges.
 *
 * Applied to: all /admin/* routes via EnsureAdminGuard OR as a separate middleware alias.
 */
class AdminIpRestriction
{
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config('plantix.admin_ip_whitelist', []);

        // No whitelist configured — allow all
        if (empty($whitelist)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        foreach ($whitelist as $allowed) {
            if ($this->ipMatches($clientIp, trim($allowed))) {
                return $next($request);
            }
        }

        // Log unauthorized admin access attempt
        logger()->warning('Admin panel access blocked by IP restriction', [
            'ip'  => $clientIp,
            'uri' => $request->getRequestUri(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
                'code'    => 'IP_RESTRICTED',
                'errors'  => [],
            ], 403);
        }

        abort(403, 'Access denied from your IP address.');
    }

    private function ipMatches(string $ip, string $allowed): bool
    {
        // Exact match
        if ($ip === $allowed) {
            return true;
        }

        // CIDR range — e.g. 10.0.0.0/24
        if (str_contains($allowed, '/')) {
            return $this->ipInCidr($ip, $allowed);
        }

        return false;
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr, 2);
        $bits = (int) $bits;

        // Only supports IPv4 CIDR for now
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask       = $bits === 0 ? 0 : (~0 << (32 - $bits));

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }
}
