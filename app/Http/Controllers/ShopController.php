<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    private const PRODUCTS_PER_PAGE = 12;

    public function index(Request $request): View|JsonResponse
    {
        $products = $this->storefrontProductsQuery($request)
            ->paginate(self::PRODUCTS_PER_PAGE)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('storefront.partials.product-grid-items', compact('products'))->render(),
                'has_more' => $products->hasMorePages(),
                'next_page' => $products->hasMorePages() ? $products->currentPage() + 1 : null,
            ]);
        }

        $categoryTree = Category::tree();

        $activeCategory = $request->filled('category')
            ? Category::query()->with(['parent', 'children'])->find($request->integer('category'))
            : null;

        return view('storefront.shop.index', compact('products', 'categoryTree', 'activeCategory'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->isVisibleOnStorefront(), 404);

        $product->load(['category.parent', 'images', 'variants' => fn ($query) => $query->where('is_active', true)->orderBy('size')]);

        $relatedProducts = Product::query()
            ->with(['images', 'variants' => fn ($query) => $query->where('is_active', true)])
            ->visibleOnStorefront()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('storefront.shop.show', compact('product', 'relatedProducts'));
    }

    private function storefrontProductsQuery(Request $request): Builder
    {
        return Product::query()
            ->with([
                'category',
                'images',
                'variants' => fn ($query) => $query->where('is_active', true),
            ])
            ->visibleOnStorefront()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->toString();
                $query->where(function ($builder) use ($search) {
                    $builder->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $category = Category::query()
                    ->with(['children' => fn ($childQuery) => $childQuery->forShopDisplay()])
                    ->find($request->integer('category'));

                if (! $category) {
                    return;
                }

                $query->whereIn('category_id', $category->filterableProductCategoryIds());
            })
            ->when($request->filled('min_price'), fn ($query) => $query->where('price', '>=', $request->float('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->float('max_price')))
            ->when($request->boolean('in_stock'), fn ($query) => $query->where(function ($builder) {
                $builder->where('quantity', '>', 0)
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery->where('is_active', true)->where('quantity', '>', 0));
            }))
            ->latest();
    }
}
