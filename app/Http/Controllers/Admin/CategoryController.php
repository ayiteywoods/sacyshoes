<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\NavbarCategoryRequest;
use App\Http\Requests\Admin\ShopCategoryOrderRequest;
use App\Models\Category;
use App\Support\AdminTable;
use App\Support\ImageUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = AdminTable::paginate(
            Category::query()
                ->with('parent')
                ->withCount(['products', 'children'])
                ->orderByRaw('COALESCE(parent_id, id)')
                ->orderByRaw('parent_id IS NOT NULL')
                ->orderBy('name'),
            $request,
            [
                'name' => 'name',
                'products_count' => 'products_count',
                'status' => 'status',
                'created_at' => 'created_at',
            ],
        );

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'parentCategories' => $this->parentCategoryOptions(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $this->categoryDataFromRequest($request);

        if ($request->hasFile('image')) {
            $data['image'] = ImageUpload::store($request->file('image'), 'categories', 5120, 1600);
        }

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $this->parentCategoryOptions($category),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $this->categoryDataFromRequest($request);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = ImageUpload::store($request->file('image'), 'categories', 5120, 1600);
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->children()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Remove or reassign subcategories before deleting this main category.');
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    public function navbar(): View
    {
        $categories = Category::query()
            ->parents()
            ->where('status', CategoryStatus::Active)
            ->orderBy('navbar_sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.navbar', compact('categories'));
    }

    public function updateNavbar(NavbarCategoryRequest $request): RedirectResponse
    {
        foreach ($request->navbarCategories() as $row) {
            Category::query()
                ->whereKey($row['id'])
                ->update([
                    'show_in_navbar' => $row['show_in_navbar'],
                    'navbar_sort_order' => $row['navbar_sort_order'],
                ]);
        }

        return redirect()
            ->route('admin.categories.navbar')
            ->with('success', 'Storefront navbar categories updated.');
    }

    public function shop(): View
    {
        $categories = Category::query()
            ->parents()
            ->where('status', CategoryStatus::Active)
            ->with(['children' => fn ($query) => $query
                ->where('status', CategoryStatus::Active)
                ->orderBy('shop_sort_order')
                ->orderBy('name')])
            ->orderBy('shop_sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.shop', compact('categories'));
    }

    public function updateShop(ShopCategoryOrderRequest $request): RedirectResponse
    {
        foreach ($request->orderedCategories() as $row) {
            Category::query()
                ->whereKey($row['id'])
                ->update([
                    'shop_sort_order' => $row['shop_sort_order'],
                ]);
        }

        return redirect()
            ->route('admin.categories.shop')
            ->with('success', 'Shop category display order updated.');
    }

    /**
     * @return Collection<int, Category>
     */
    protected function parentCategoryOptions(?Category $exclude = null)
    {
        return Category::query()
            ->parents()
            ->when($exclude, fn ($query) => $query->whereKeyNot($exclude->id))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    protected function categoryDataFromRequest(CategoryRequest $request): array
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['parent_id'] = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;
        $data['show_in_navbar'] = $request->boolean('show_in_navbar');
        $data['navbar_sort_order'] = (int) ($data['navbar_sort_order'] ?? 0);

        if ($data['parent_id']) {
            $data['show_in_navbar'] = false;
            $data['navbar_sort_order'] = 0;
        }

        return $data;
    }
}
