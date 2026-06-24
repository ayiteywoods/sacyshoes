<?php

namespace App\Support;

use App\Models\Order;

class OrderNumberGenerator
{
    public static function startingSequence(): int
    {
        return max(1, (int) config('shop.order_number_start', 1000));
    }

    public static function format(int $sequence): string
    {
        $padding = max(1, (int) config('shop.order_number_padding', 4));

        return str_pad((string) $sequence, $padding, '0', STR_PAD_LEFT);
    }

    public static function next(): string
    {
        Order::query()->orderByDesc('id')->lockForUpdate()->first();

        $highestAssigned = (int) (Order::query()
            ->pluck('order_number')
            ->map(fn (string $orderNumber) => (int) ltrim($orderNumber, '0'))
            ->max() ?? 0);

        $nextSequence = max($highestAssigned + 1, self::startingSequence());

        return self::format($nextSequence);
    }

    public static function fromOrderId(int $orderId): string
    {
        return self::format(self::startingSequence() + max(0, $orderId - 1));
    }

    public static function renumberAll(): int
    {
        $orders = Order::query()->orderBy('id')->pluck('id');
        $count = $orders->count();

        if ($count === 0) {
            return 0;
        }

        foreach ($orders as $id) {
            Order::query()
                ->whereKey($id)
                ->update(['order_number' => 'renumber-'.$id]);
        }

        $start = self::startingSequence();

        foreach ($orders->values() as $index => $id) {
            Order::query()
                ->whereKey($id)
                ->update(['order_number' => self::format($start + $index)]);
        }

        return $count;
    }
}
