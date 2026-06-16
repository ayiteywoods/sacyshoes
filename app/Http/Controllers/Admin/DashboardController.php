<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminActivityService;
use App\Services\AdminNotificationService;
use App\Services\AdminReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
        $topSelling = $reports->topSellingProducts(5);

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get();

        $lowStockProducts = Product::query()
            ->with('category')
            ->where('status', ProductStatus::Active)
            ->where('quantity', '<', 10)
            ->orderBy('quantity')
            ->limit(5)
            ->get();

        $notificationsList = $notifications->get();
        $recentActivity = $activity->recent(12);
        $recentCustomers = $reports->recentCustomersForPeriod($from, $to, 8);

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
            'greeting' => $greeting,
        ]);
    }
}
