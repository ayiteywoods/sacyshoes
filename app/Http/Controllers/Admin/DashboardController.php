<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\AdminActivityService;
use App\Services\AdminNotificationService;
use App\Services\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const LIST_PER_PAGE = 5;

    public function __invoke(Request $request, AdminReportService $reports, AdminNotificationService $notifications, AdminActivityService $activity): View
    {
        $period = in_array($request->string('period')->toString(), ['today', '7d', '30d', 'month'], true)
            ? $request->string('period')->toString()
            : '30d';

        [$from, $to] = $reports->dashboardPeriodBounds($period);
        $periodStats = $reports->dashboardStatsForPeriod($from, $to);
        $comparison = $reports->dashboardPeriodComparison($from, $to);
        $attention = $reports->attentionMetrics();
        $orderStatusBreakdown = $reports->orderStatusBreakdown();
        $paymentStatusBreakdown = $reports->paymentStatusBreakdown();
        $stats = $reports->dashboardStats();

        $chartDays = $reports->chartDaysForPeriod($period);
        $dailyChart = $reports->dailySalesChart($chartDays);
        $monthlyChart = $reports->monthlySalesChart(12);

        $topSelling = $reports->topSellingProductsQuery()
            ->orderByDesc('units_sold')
            ->paginate(self::LIST_PER_PAGE, ['*'], 'top_selling_page')
            ->withQueryString();

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->paginate(self::LIST_PER_PAGE, ['*'], 'orders_page')
            ->withQueryString();

        $lowStockProducts = Product::query()
            ->with('category')
            ->where('status', ProductStatus::Active)
            ->where('quantity', '<', 10)
            ->orderBy('quantity')
            ->paginate(self::LIST_PER_PAGE, ['*'], 'low_stock_page')
            ->withQueryString();

        $notificationsList = $notifications->paginated(self::LIST_PER_PAGE);
        $recentActivity = $activity->paginated(self::LIST_PER_PAGE)->withQueryString();
        $recentCustomers = $reports->recentCustomersPaginator($from, $to, self::LIST_PER_PAGE);

        $ordersToFulfill = Order::query()
            ->with('user')
            ->where('payment_status', PaymentStatus::Paid)
            ->whereIn('status', [
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::ReadyForDelivery,
            ])
            ->latest('paid_at')
            ->paginate(self::LIST_PER_PAGE, ['*'], 'orders_fulfill_page')
            ->withQueryString();

        $greeting = match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return view('admin.dashboard', [
            'period' => $period,
            'periodLabel' => $reports->dashboardPeriodLabel($period),
            'periodStats' => $periodStats,
            'comparison' => $comparison,
            'attention' => $attention,
            'orderStatusBreakdown' => $orderStatusBreakdown,
            'paymentStatusBreakdown' => $paymentStatusBreakdown,
            'stats' => $stats,
            'dailyChart' => $dailyChart,
            'monthlyChart' => $monthlyChart,
            'topSelling' => $topSelling,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'notifications' => $notificationsList,
            'recentActivity' => $recentActivity,
            'recentCustomers' => $recentCustomers,
            'ordersToFulfill' => $ordersToFulfill,
            'greeting' => $greeting,
            'storeSettings' => StoreSetting::current(),
        ]);
    }
}
