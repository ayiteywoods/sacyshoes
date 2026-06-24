<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function dashboardStats(): array
    {
        $paidQuery = Order::query()->where('payment_status', PaymentStatus::Paid);

        $weekSales = (clone $paidQuery)
            ->where('paid_at', '>=', now()->startOfWeek())
            ->sum('total');

        $monthSales = (clone $paidQuery)
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('total');

        return [
            'total_sales' => (float) (clone $paidQuery)->sum('total'),
            'today_sales' => (float) (clone $paidQuery)->whereDate('paid_at', today())->sum('total'),
            'week_sales' => (float) $weekSales,
            'month_sales' => (float) $monthSales,
            'total_orders' => Order::count(),
            'paid_orders' => (clone $paidQuery)->count(),
            'total_products' => Product::count(),
            'total_customers' => User::query()->where('role', UserRole::Customer)->count(),
            'low_stock_products' => Product::query()
                ->where('quantity', '<', 10)
                ->where('status', ProductStatus::Active)
                ->count(),
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function dashboardPeriodBounds(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            '7d' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'month' => [now()->startOfMonth(), now()->endOfDay()],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
        };
    }

    public function dashboardPeriodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Today',
            '7d' => 'Last 7 days',
            'month' => 'This month',
            default => 'Last 30 days',
        };
    }

    /**
     * @return array{revenue: float, orders: int, average_order: float, new_customers: int}
     */
    public function dashboardStatsForPeriod(Carbon $from, Carbon $to): array
    {
        $paidOrders = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $revenue = (float) (clone $paidOrders)->sum('total');
        $orderCount = (clone $paidOrders)->count();

        return [
            'revenue' => $revenue,
            'orders' => $orderCount,
            'average_order' => $orderCount > 0 ? round($revenue / $orderCount, 2) : 0.0,
            'new_customers' => User::query()
                ->where('role', UserRole::Customer)
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->count(),
        ];
    }

    /**
     * @return array{revenue_change: ?float, orders_change: ?float, average_order_change: ?float, customers_change: ?float}
     */
    public function dashboardPeriodComparison(Carbon $from, Carbon $to): array
    {
        $days = max($from->copy()->startOfDay()->diffInDays($to->copy()->endOfDay()) + 1, 1);
        $previousFrom = $from->copy()->subDays($days)->startOfDay();
        $previousTo = $from->copy()->subSecond();

        $current = $this->dashboardStatsForPeriod($from, $to);
        $previous = $this->dashboardStatsForPeriod($previousFrom, $previousTo);

        return [
            'revenue_change' => $this->percentChange($previous['revenue'], $current['revenue']),
            'orders_change' => $this->percentChange((float) $previous['orders'], (float) $current['orders']),
            'average_order_change' => $this->percentChange($previous['average_order'], $current['average_order']),
            'customers_change' => $this->percentChange((float) $previous['new_customers'], (float) $current['new_customers']),
        ];
    }

    /**
     * @return array{pending_payment: int, needs_fulfillment: int, low_stock: int, new_customers_today: int, failed_payments: int}
     */
    public function attentionMetrics(): array
    {
        return [
            'pending_payment' => Order::query()
                ->where('payment_status', PaymentStatus::Pending)
                ->where('status', OrderStatus::PendingPayment)
                ->count(),
            'needs_fulfillment' => Order::query()
                ->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing])
                ->count(),
            'low_stock' => Product::query()
                ->where('status', ProductStatus::Active)
                ->where('quantity', '<', 10)
                ->count(),
            'new_customers_today' => User::query()
                ->where('role', UserRole::Customer)
                ->whereDate('created_at', today())
                ->count(),
            'failed_payments' => Order::query()
                ->where('payment_status', PaymentStatus::Failed)
                ->count(),
        ];
    }

    /**
     * @return Collection<int, array{status: OrderStatus, label: string, count: int}>
     */
    public function orderStatusBreakdown(): Collection
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(OrderStatus::cases())
            ->map(fn (OrderStatus $status) => [
                'status' => $status,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    /**
     * @return Collection<int, array{status: PaymentStatus, label: string, count: int}>
     */
    public function paymentStatusBreakdown(): Collection
    {
        $counts = Order::query()
            ->selectRaw('payment_status, COUNT(*) as total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status');

        return collect(PaymentStatus::cases())
            ->map(fn (PaymentStatus $status) => [
                'status' => $status,
                'label' => $status->label(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    public function chartDaysForPeriod(string $period): int
    {
        return match ($period) {
            'today' => 1,
            '7d' => 7,
            'month' => max(now()->day, 1),
            default => 30,
        };
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    public function recentCustomersForPeriod(Carbon $from, Carbon $to, int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function recentCustomersPaginator(Carbon $from, Carbon $to, int $perPage = 5): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return User::query()
            ->where('role', UserRole::Customer)
            ->withCount('orders')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->latest()
            ->paginate($perPage, ['*'], 'customers_page')
            ->withQueryString();
    }

    private function percentChange(float $previous, float $current): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * @return array{labels: array<int, string>, revenue: array<int, float>, orders: array<int, int>}
     */
    public function dailySalesChart(int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->where('paid_at', '>=', $start)
            ->selectRaw('DATE(paid_at) as sale_date, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $labels = [];
        $revenue = [];
        $orders = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('M j');
            $revenue[] = round((float) ($rows[$key]->revenue ?? 0), 2);
            $orders[] = (int) ($rows[$key]->orders ?? 0);
        }

        return compact('labels', 'revenue', 'orders');
    }

    /**
     * @return array{labels: array<int, string>, revenue: array<int, float>}
     */
    public function monthlySalesChart(int $months = 12): array
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $rows = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->where('paid_at', '>=', $start)
            ->selectRaw('YEAR(paid_at) as year, MONTH(paid_at) as month, SUM(total) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->year, $row->month));

        $labels = [];
        $revenue = [];

        for ($i = 0; $i < $months; $i++) {
            $date = $start->copy()->addMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            $revenue[] = round((float) ($rows[$key]->revenue ?? 0), 2);
        }

        return compact('labels', 'revenue');
    }

    /**
     * @return Collection<int, object{product_name: string, units_sold: int, revenue: float}>
     */
    public function topSellingProducts(int $limit = 5): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.total_price) as revenue'),
            ])
            ->groupBy('order_items.product_name')
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array{revenue: float, orders: int, transactions: int, average_order: float}
     */
    public function periodSummary(Carbon $from, Carbon $to): array
    {
        $orders = Order::query()
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->get(['total']);

        $count = $orders->count();
        $revenue = (float) $orders->sum('total');

        return [
            'revenue' => $revenue,
            'orders' => $count,
            'transactions' => $count,
            'average_order' => $count > 0 ? round($revenue / $count, 2) : 0.0,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Order>
     */
    public function ordersForPeriodQuery(Carbon $from, Carbon $to): \Illuminate\Database\Eloquent\Builder
    {
        return Order::query()
            ->with('user')
            ->where('payment_status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    /**
     * @return Collection<int, Order>
     */
    public function ordersForPeriod(Carbon $from, Carbon $to): Collection
    {
        return $this->ordersForPeriodQuery($from, $to)
            ->orderByDesc('paid_at')
            ->get();
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function topSellingProductsQuery(): \Illuminate\Database\Query\Builder
    {
        $subQuery = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->select([
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.total_price) as revenue'),
            ])
            ->groupBy('order_items.product_name');

        return DB::query()->fromSub($subQuery, 'top_selling');
    }

    public function growthRate(Carbon $from, Carbon $to): ?float
    {
        $days = max($from->diffInDays($to) + 1, 1);
        $previousFrom = $from->copy()->subDays($days);
        $previousTo = $from->copy()->subDay();

        $current = $this->periodSummary($from, $to)['revenue'];
        $previous = $this->periodSummary($previousFrom, $previousTo)['revenue'];

        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
