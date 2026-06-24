<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\AdminTable;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        $categoryTree = Category::tree();

        return view('admin.products.create', compact('categoryTree'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['variants'], $data['publish_date'], $data['publish_time']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['quantity'] = 0;

        $product = Product::create($data);

        $this->syncVariants($product, $variants);
        $this->storeImages($product, $request->uploadedImages());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categoryTree = Category::tree();
        $product->load(['images', 'variants']);

        return view('admin.products.edit', compact('product', 'categoryTree'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $variants = $data['variants'] ?? [];
        unset($data['images'], $data['variants'], $data['publish_date'], $data['publish_time']);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product->update($data);

        $this->syncVariants($product, $variants);
        $this->storeImages($product, $request->uploadedImages());

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
     * @param  list<array<string, mixed>>  $variants
     */
    private function syncVariants(Product $product, array $variants): void
    {
        $keptIds = [];

        foreach ($variants as $variantData) {
            $variant = isset($variantData['id'])
                ? $product->variants()->whereKey($variantData['id'])->first()
                : new ProductVariant(['product_id' => $product->id]);

            if (! $variant) {
                continue;
            }

            $sku = $variantData['sku'] ?? $this->generateVariantSku($product, $variantData, $variant->id);

            $heelLength = filled($variantData['heel_length'] ?? null)
                ? $variantData['heel_length']
                : null;

            $variant->fill([
                'sku' => $sku,
                'size' => $variantData['size'],
                'color' => $variantData['color'],
                'heel_length' => $heelLength,
                'quantity' => (int) $variantData['quantity'],
                'is_active' => (bool) ($variantData['is_active'] ?? true),
            ])->save();

            $keptIds[] = $variant->id;
        }

        $product->variants()->whereNotIn('id', $keptIds)->delete();

        $product->update([
            'quantity' => (int) $product->variants()->sum('quantity'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $variantData
     */
    private function generateVariantSku(Product $product, array $variantData, ?int $ignoreId = null): string
    {
        $base = strtoupper(Str::slug($product->sku, ''));
        $size = strtoupper(Str::slug($variantData['size'], ''));
        $color = strtoupper(Str::slug($variantData['color'], ''));
        $parts = [$base, $size, $color];

        if (filled($variantData['heel_length'] ?? null)) {
            $parts[] = strtoupper(Str::slug($variantData['heel_length'], ''));
        }

        $sku = implode('-', $parts);
        $suffix = 1;

        while (
            ProductVariant::query()
                ->where('sku', $sku)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $sku = implode('-', [...$parts, (string) $suffix]);
            $suffix++;
        }

        return $sku;
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            if (! $image->isValid()) {
                continue;
            }

            $path = ImageUpload::store($image, 'products', 4096, 2000);

            ProductImage::create([
                'product_id' => $product->id,
                'path' => $path,
                'is_primary' => $product->images()->count() === 0 && $index === 0,
                'sort_order' => $product->images()->count() + $index,
            ]);
        }
    }
}
