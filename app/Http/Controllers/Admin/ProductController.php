<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\AdminTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = AdminTable::paginate(
            Product::query()->with(['category', 'images']),
            $request,
            [
                'name' => 'name',
                'sku' => 'sku',
                'price' => 'price',
                'quantity' => 'quantity',
                'status' => 'status',
                'created_at' => 'created_at',
            ],
        );

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['images']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create($data);

        $this->storeImages($product, $request->file('images', []));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $product->load('images');

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        unset($data['images']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product->update($data);

        $this->storeImages($product, $request->file('images', []));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            $path = $image->store('products', 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_primary' => $product->images()->count() === 0 && $index === 0,
                'sort_order' => $product->images()->count() + $index,
            ]);
        }
    }
}
