<?php

namespace App\Providers;

use App\Enums\AdminPermission;
use App\View\Composers\StorefrontComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        View::composer('layouts.storefront', StorefrontComposer::class);

        Blade::if('adminCan', function (string $permission): bool {
            $user = auth()->user();

            if (! $user) {
                return false;
            }

            $enum = AdminPermission::tryFrom($permission);

            return $enum ? $user->hasAdminPermission($enum) : false;
        });
    }
}
