<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminActivityService
{
    /**
     * @return Collection<int, array{occurred_at: Carbon, type: string, title: string, message: string, url: ?string}>
     */
    public function recent(int $limit = 12): Collection
    {
        $events = collect();

        Order::query()
            ->latest()
            ->limit(8)
            ->get()
            ->each(function (Order $order) use ($events) {
                $events->push([
                    'occurred_at' => $order->created_at,
                    'type' => 'order_placed',
                    'title' => 'Order placed',
                    'message' => $order->order_number.' · '.($order->user?->name ?? $order->billing_full_name),
                    'url' => route('admin.orders.show', $order),
                ]);
            });

        Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereNotNull('paid_at')
            ->latest('paid_at')
            ->limit(8)
            ->get()
            ->each(function (Order $order) use ($events) {
                $events->push([
                    'occurred_at' => $order->paid_at,
                    'type' => 'order_paid',
                    'title' => 'Payment received',
                    'message' => $order->order_number.' · GHS '.number_format((float) $order->total, 2),
                    'url' => route('admin.orders.show', $order),
                ]);
            });

        User::query()
            ->where('role', UserRole::Customer)
            ->latest()
            ->limit(8)
            ->get()
            ->each(function (User $user) use ($events) {
                $events->push([
                    'occurred_at' => $user->created_at,
                    'type' => 'customer_registered',
                    'title' => 'New customer',
                    'message' => $user->name.' · '.$user->email,
                    'url' => route('admin.customers.index'),
                ]);
            });

        AdminNotification::query()
            ->whereNull('dismissed_at')
            ->latest()
            ->limit(6)
            ->get()
            ->each(function (AdminNotification $notification) use ($events) {
                $events->push([
                    'occurred_at' => $notification->created_at,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => $notification->url,
                ]);
            });

        return $events
            ->sortByDesc(fn (array $event) => $event['occurred_at'])
            ->take($limit)
            ->values();
    }
}
