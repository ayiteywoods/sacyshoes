<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingRegionRequest;
use App\Models\ShippingOption;
use App\Models\ShippingRegion;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShippingRegionController extends Controller
{
    public function index(): View
    {
        $regions = ShippingRegion::query()
            ->withCount(['options'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.shipping-regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('admin.shipping-regions.create');
    }

    public function store(ShippingRegionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        $region = ShippingRegion::query()->create([
            'name' => $data['name'],
            'is_accra' => (bool) ($data['is_accra'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->syncOptions($region, $options);

        return redirect()
            ->route('admin.shipping-regions.index')
            ->with('success', 'Shipping region created.');
    }

    public function edit(ShippingRegion $shipping_region): View
    {
        $shipping_region->load('options');

        return view('admin.shipping-regions.edit', [
            'region' => $shipping_region,
        ]);
    }

    public function update(ShippingRegionRequest $request, ShippingRegion $shipping_region): RedirectResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        $shipping_region->update([
            'name' => $data['name'],
            'is_accra' => (bool) ($data['is_accra'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);

        $this->syncOptions($shipping_region, $options);

        return redirect()
            ->route('admin.shipping-regions.index')
            ->with('success', 'Shipping region updated.');
    }

    public function destroy(ShippingRegion $shipping_region): RedirectResponse
    {
        $shipping_region->delete();

        return redirect()
            ->route('admin.shipping-regions.index')
            ->with('success', 'Shipping region deleted.');
    }

    /**
     * @param  list<array<string, mixed>>  $options
     */
    private function syncOptions(ShippingRegion $region, array $options): void
    {
        $keptIds = [];

        foreach ($options as $optionData) {
            $option = isset($optionData['id'])
                ? $region->options()->whereKey($optionData['id'])->first()
                : new ShippingOption(['shipping_region_id' => $region->id]);

            if (! $option) {
                continue;
            }

            $option->fill([
                'name' => $optionData['name'],
                'price' => $optionData['price'],
                'description' => $optionData['description'] ?? null,
                'is_active' => (bool) ($optionData['is_active'] ?? true),
                'sort_order' => (int) ($optionData['sort_order'] ?? 0),
            ])->save();

            $keptIds[] = $option->id;
        }

        $region->options()->whereNotIn('id', $keptIds)->delete();
    }
}
