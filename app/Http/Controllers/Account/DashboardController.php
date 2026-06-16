<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $ordersQuery = Order::query()->where('user_id', $user->id);

        $stats = [
            'total' => (clone $ordersQuery)->count(),
            'pending' => (clone $ordersQuery)->whereIn('status', [
                OrderStatus::PendingPayment,
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::ReadyForDelivery,
                OrderStatus::Shipped,
            ])->count(),
            'completed' => (clone $ordersQuery)->where('status', OrderStatus::Delivered)->count(),
            'awaiting_payment' => (clone $ordersQuery)->where('payment_status', PaymentStatus::Pending)->count(),
        ];

        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return view('account.dashboard', compact('stats', 'recentOrders'));
    }
}
