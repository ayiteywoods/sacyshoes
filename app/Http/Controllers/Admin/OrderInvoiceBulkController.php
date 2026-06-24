<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OrderInvoiceBulkController extends Controller
{
    public function export(Request $request, InvoiceService $invoices): Response|RedirectResponse
    {
        $orders = $this->resolveSelectedOrders($request);

        if ($orders->isEmpty()) {
            return back()->with('error', 'Select at least one order to export invoices.');
        }

        return $invoices->downloadBulk($orders);
    }

    public function print(Request $request, InvoiceService $invoices): View|RedirectResponse
    {
        $orders = $this->resolveSelectedOrders($request);

        if ($orders->isEmpty()) {
            return back()->with('error', 'Select at least one order to print invoices.');
        }

        return $invoices->renderBulkPrint($orders, 0);
    }

    /**
     * @return Collection<int, Order>
     */
    protected function resolveSelectedOrders(Request $request): Collection
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:50'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $requestedIds = collect($validated['order_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $ordersById = Order::query()
            ->with('items')
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        return $requestedIds
            ->map(fn (int $id) => $ordersById->get($id))
            ->filter()
            ->values();
    }
}
