<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Services\HomeContentService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomeContentService $content): View
    {
        $featuredProducts = Product::query()
            ->with(['category', 'images'])
            ->where('status', ProductStatus::Active)
            ->latest()
            ->limit(8)
            ->get();

        $categories = Category::query()
            ->where('status', 'active')
            ->withCount('products')
            ->orderBy('name')
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
}
