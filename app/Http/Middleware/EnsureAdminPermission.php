<?php

namespace App\Http\Middleware;

use App\Enums\AdminPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin() || ! $user->is_active) {
            abort(403, 'Unauthorized access.');
        }

        $required = collect($permissions)
            ->map(fn (string $permission) => AdminPermission::tryFrom($permission))
            ->filter()
            ->all();

        foreach ($required as $permission) {
            if ($user->hasAdminPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
