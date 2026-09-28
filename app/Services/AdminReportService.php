<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    /** @var Collection<string, int>|null */
    private ?Collection $variantStockIndex = null;

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
     * @return array{revenue: float, orders: int, average_order: float, units_sold: int, new_customers: int}
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
            'units_sold' => $this->unitsSoldForPeriod($from, $to),
            'new_customers' => User::query()
                ->where('role', UserRole::Customer)
                ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->count(),
        ];
    }

    public function unitsSoldForPeriod(Carbon $from, Carbon $to): int
    {
        return (int) $this->paidOrderItemsForPeriod($from, $to)->sum('order_items.quantity');
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
     * @return array{revenue: float, orders: int, transactions: int, average_order: float, units_sold: int}
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
            'units_sold' => $this->unitsSoldForPeriod($from, $to),
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

    /**
     * @return Collection<int, object{label: string, units_sold: int, stock_left: int}>
     */
    public function quantitiesSoldBySize(Carbon $from, Carbon $to): Collection
    {
        return $this->quantitiesSoldByOption($from, $to, 'size', 'No size');
    }

    /**
     * @return Collection<int, object{label: string, units_sold: int, stock_left: int}>
     */
    public function quantitiesSoldByColor(Carbon $from, Carbon $to): Collection
    {
        return $this->quantitiesSoldByOption($from, $to, 'color', 'No color');
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return LengthAwarePaginator<int, object>
     */
    public function paginateQuantityRows(Collection $rows, int $perPage, string $pageName): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ],
        );
    }

    /**
     * @return Collection<int, object{product_name: string, size: string, color: string, units_sold: int, stock_left: int, revenue: float}>
     */
    public function quantitiesSoldByVariant(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return $this->quantitiesSoldByVariantQuery($from, $to)
            ->orderByDesc('units_sold')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => $this->presentVariantSale($row));
    }

    public function presentVariantSale(object $row): object
    {
        $size = $this->optionLabel($row->size ?? null, 'No size');
        $color = $this->optionLabel($row->color ?? null, 'No color');
        $productId = isset($row->product_id) ? (int) $row->product_id : 0;

        return (object) [
            'product_id' => $productId,
            'product_name' => (string) $row->product_name,
            'size' => $size,
            'color' => $color,
            'units_sold' => (int) $row->units_sold,
            'stock_left' => $this->stockLeftFor($productId, $size, $color),
            'revenue' => round((float) ($row->revenue ?? 0), 2),
        ];
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function quantitiesSoldByVariantQuery(Carbon $from, Carbon $to): \Illuminate\Database\Query\Builder
    {
        $sizeExpr = $this->variantOptionSql('size');
        $colorExpr = $this->variantOptionSql('color');

        $subQuery = $this->paidOrderItemsForPeriod($from, $to)
            ->select([
                'order_items.product_id',
                'order_items.product_name',
                DB::raw("{$sizeExpr} as size"),
                DB::raw("{$colorExpr} as color"),
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.total_price) as revenue'),
            ])
            ->groupBy('order_items.product_id')
            ->groupBy('order_items.product_name')
            ->groupByRaw($sizeExpr)
            ->groupByRaw($colorExpr);

        return DB::query()->fromSub($subQuery, 'variant_sales');
    }

    /**
     * @return Collection<int, object{product_name: string, size: string, color: string, units_sold: int, stock_left: int, revenue: float}>
     */
    public function quantitiesSoldByVariantAll(Carbon $from, Carbon $to): Collection
    {
        return $this->quantitiesSoldByVariantQuery($from, $to)
            ->orderByDesc('units_sold')
            ->get()
            ->map(fn ($row) => $this->presentVariantSale($row));
    }

    /**
     * @return Collection<int, object{label: string, units_sold: int, stock_left: int}>
     */
    private function quantitiesSoldByOption(Carbon $from, Carbon $to, string $option, string $emptyLabel): Collection
    {
        $expr = $this->variantOptionSql($option);
        $stockByLabel = $this->stockByOption($option, $emptyLabel);

        $sold = $this->paidOrderItemsForPeriod($from, $to)
            ->selectRaw("{$expr} as option_value")
            ->selectRaw('SUM(order_items.quantity) as units_sold')
            ->groupByRaw($expr)
            ->orderByDesc('units_sold')
            ->get()
            ->map(fn ($row) => (object) [
                'label' => $this->optionLabel($row->option_value, $emptyLabel),
                'units_sold' => (int) $row->units_sold,
            ])
            ->groupBy('label')
            ->map(fn (Collection $rows) => (int) $rows->sum('units_sold'));

        return $sold->keys()
            ->merge($stockByLabel->keys())
            ->unique()
            ->map(fn (string $label) => (object) [
                'label' => $label,
                'units_sold' => (int) ($sold[$label] ?? 0),
                'stock_left' => (int) ($stockByLabel[$label] ?? 0),
            ])
            ->sortByDesc('units_sold')
            ->values();
    }

    /**
     * @return Collection<string, int>
     */
    private function stockByOption(string $option, string $emptyLabel): Collection
    {
        return ProductVariant::query()
            ->select($option)
            ->selectRaw('SUM(quantity) as stock_left')
            ->groupBy($option)
            ->get()
            ->mapWithKeys(fn ($row) => [
                $this->optionLabel($row->{$option}, $emptyLabel) => (int) $row->stock_left,
            ]);
    }

    private function stockLeftFor(int $productId, string $size, string $color): int
    {
        return (int) ($this->variantStockMap()[$this->inventoryKey($productId, $size, $color)] ?? 0);
    }

    /**
     * @return Collection<string, int>
     */
    private function variantStockMap(): Collection
    {
        return $this->variantStockIndex ??= ProductVariant::query()
            ->get(['product_id', 'size', 'color', 'quantity'])
            ->groupBy(fn (ProductVariant $variant) => $this->inventoryKey(
                (int) $variant->product_id,
                $variant->size,
                $variant->color,
            ))
            ->map(fn (Collection $rows) => (int) $rows->sum('quantity'));
    }

    private function inventoryKey(int $productId, mixed $size, mixed $color): string
    {
        return strtolower(implode('|', [
            (string) $productId,
            $this->optionLabel($size, 'No size'),
            $this->optionLabel($color, 'No color'),
        ]));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<OrderItem>
     */
    private function paidOrderItemsForPeriod(Carbon $from, Carbon $to): \Illuminate\Database\Eloquent\Builder
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->whereBetween('orders.paid_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    private function variantOptionSql(string $key): string
    {
        $path = '$.'.$key;

        return match (DB::connection()->getDriverName()) {
            'sqlite' => "json_extract(order_items.variant_options, '{$path}')",
            'pgsql' => "(order_items.variant_options->>'{$key}')",
            default => "JSON_UNQUOTE(JSON_EXTRACT(order_items.variant_options, '{$path}'))",
        };
    }

    private function optionLabel(mixed $value, string $emptyLabel): string
    {
        $label = trim((string) $value);

        return $label === '' || $label === 'null' ? $emptyLabel : $label;
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
