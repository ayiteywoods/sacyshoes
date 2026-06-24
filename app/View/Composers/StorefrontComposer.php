<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Page;
use App\Models\StoreSetting;
use App\Services\CartService;
use App\Services\FavoriteService;
use Illuminate\View\View;

class StorefrontComposer
{
    public function __construct(
        protected CartService $cart,
        protected FavoriteService $favorites,
    ) {}

    public function compose(View $view): void
    {
        $navbarCategories = Category::query()->forNavbar()->get();

        $view->with('navbarCategories', $navbarCategories);
        $view->with('floatingCategories', $navbarCategories);

        $view->with('cartCount', $this->cart->count());
        $view->with('favoriteProductIds', $this->favorites->productIdsFor(auth()->user()));

        $view->with('footerCustomerCarePages', Page::query()
            ->where('footer_group', Page::FOOTER_CUSTOMER_CARE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get());

        $view->with('footerLegalPages', Page::query()
            ->where('footer_group', Page::FOOTER_LEGAL)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get());

        $view->with('socialLinks', StoreSetting::current()->socialLinks());

        $storeSettings = StoreSetting::current();
        $view->with('footerTagline', $storeSettings->footerTagline());
        $view->with('footerSubline', $storeSettings->footerSubline());
    }
}
