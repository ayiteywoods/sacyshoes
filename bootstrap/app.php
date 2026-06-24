<?php

use App\Http\Middleware\EnsureAdminPermission;
use App\Http\Middleware\EnsureCartNotEmpty;
use App\Http\Middleware\EnsureStorefrontNotInMaintenance;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'admin.permission' => EnsureAdminPermission::class,
            'cart.not_empty' => EnsureCartNotEmpty::class,
            'storefront.maintenance' => EnsureStorefrontNotInMaintenance::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'paystack/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
