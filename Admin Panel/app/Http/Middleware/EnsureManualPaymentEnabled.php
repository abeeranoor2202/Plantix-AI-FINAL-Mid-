<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureManualPaymentEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('payment.manual_payment_enabled')) {
            Log::warning('Manual payment attempted while disabled', [
                'path' => $request->path(),
                'user_id' => optional($request->user('web'))->id,
                'ip' => $request->ip(),
            ]);

            abort(404);
        }

        return $next($request);
    }
}
