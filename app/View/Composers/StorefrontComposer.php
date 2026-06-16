<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Page;
use App\Services\CartService;
use Illuminate\View\View;

class StorefrontComposer
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function compose(View $view): void
    {
        $view->with('floatingCategories', Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get());

        $view->with('cartCount', $this->cart->count());

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
    }
}
