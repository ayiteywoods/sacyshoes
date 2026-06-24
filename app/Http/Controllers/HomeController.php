<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\HomeContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const NEW_ARRIVALS_PER_PAGE = 8;

    public function __invoke(HomeContentService $content): View
    {
        $featuredProducts = $this->newArrivalsQuery()
            ->paginate(self::NEW_ARRIVALS_PER_PAGE);

        $categories = Category::query()
            ->parents()
            ->withCount('products')
            ->forShopDisplay()
            ->get();

        $sections = $content->sections();
        $testimonials = $content->testimonials();

        return view('storefront.home', compact(
            'featuredProducts',
            'categories',
            'sections',
            'testimonials',
        ));
    }

    public function newArrivals(Request $request): JsonResponse
    {
        $products = $this->newArrivalsQuery()
            ->paginate(self::NEW_ARRIVALS_PER_PAGE, ['*'], 'page', max(1, $request->integer('page', 1)));

        return response()->json([
            'html' => view('storefront.partials.product-grid-items', compact('products'))->render(),
            'has_more' => $products->hasMorePages(),
            'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
        ]);
    }

    private function newArrivalsQuery()
    {
        return Product::query()
            ->with([
                'category',
                'images',
                'variants' => fn ($query) => $query->where('is_active', true),
            ])
            ->visibleOnStorefront()
            ->latest();
    }
}
