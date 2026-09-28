<?php

namespace App\Providers;

use App\Enums\AdminPermission;
use App\Models\CartItem;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\CartService;
use App\Services\StoreSettingService;
use App\View\Composers\StorefrontComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
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
        $this->forceHttpsWhenNeeded();

        app(StoreSettingService::class)->applyToConfig();

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

        Order::observe(OrderObserver::class);

        Route::bind('cartItem', function (string $value): CartItem {
            $cart = app(CartService::class)->resolve();

            return CartItem::query()
                ->where('cart_id', $cart->id)
                ->whereKey($value)
                ->firstOrFail();
        });
    }

    private function forceHttpsWhenNeeded(): void
    {
        if (! str_starts_with((string) config('app.url'), 'https://')) {
            return;
        }

        if ($this->app->runningInConsole()) {
            URL::forceScheme('https');

            return;
        }

        $host = request()->getHost();

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return;
        }

        URL::forceScheme('https');
    }
}
