<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('account.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.product', 'payment']);

        return view('account.orders.show', [
            'order' => $order,
            'trackingSteps' => $order->trackingSteps(),
            'accountHeroStats' => [
                ['value' => $order->status->label(), 'label' => 'Status', 'icon' => 'orders', 'tone' => 'red'],
                ['value' => config('shop.currency_symbol').' '.number_format($order->total, 0), 'label' => 'Total', 'icon' => 'bag', 'tone' => 'white'],
                ['value' => $order->items->count(), 'label' => 'Items', 'icon' => 'cart', 'tone' => 'red'],
            ],
        ]);
    }
}
