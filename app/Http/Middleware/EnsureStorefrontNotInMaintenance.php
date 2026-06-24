<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStorefrontNotInMaintenance
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('shop.maintenance_mode', false)) {
            return $next($request);
        }

        if ($request->user()?->isAdmin()) {
            return $next($request);
        }

        if ($request->is(
            'paystack/*',
            'checkout/success/*',
            'login',
            'logout',
            'forgot-password',
            'reset-password/*',
        )) {
            return $next($request);
        }

        $message = (string) config('shop.maintenance_message');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return response()->view('storefront.maintenance', [
            'message' => $message,
        ], Response::HTTP_SERVICE_UNAVAILABLE);
    }
}
