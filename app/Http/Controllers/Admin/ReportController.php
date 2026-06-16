<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminReportService;
use App\Support\AdminTable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, AdminReportService $reports): View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $summary = $reports->periodSummary($from, $to);
        $growth = $reports->growthRate($from, $to);
        $orders = AdminTable::paginate(
            $reports->ordersForPeriodQuery($from, $to),
            $request,
            [
                'order_number' => 'order_number',
                'customer' => 'billing_full_name',
                'paid_at' => 'paid_at',
                'total' => 'total',
                'status' => 'status',
            ],
            'paid_at',
            'desc',
        );
        $topSelling = AdminTable::paginate(
            $reports->topSellingProductsQuery(),
            $request,
            [
                'product_name' => 'product_name',
                'units_sold' => 'units_sold',
                'revenue' => 'revenue',
            ],
            'units_sold',
            'desc',
            AdminTable::PER_PAGE,
            'product_sort',
            'product_direction',
            'product_page',
        );

        return view('admin.reports.index', compact(
            'from',
            'to',
            'summary',
            'growth',
            'orders',
            'topSelling',
        ));
    }

    public function export(Request $request, AdminReportService $reports): StreamedResponse|View
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now();
        $format = $request->string('format', 'csv')->toString();

        $summary = $reports->periodSummary($from, $to);
        $orders = $reports->ordersForPeriod($from, $to);

        if ($format === 'pdf') {
            return view('admin.reports.print', compact('from', 'to', 'summary', 'orders'));
        }

        $filename = 'sacyshoes-sales-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($orders, $summary, $from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Sacy Shoes Sales Report']);
            fputcsv($handle, ['Period', $from->format('M j, Y').' - '.$to->format('M j, Y')]);
            fputcsv($handle, ['Revenue', number_format($summary['revenue'], 2)]);
            fputcsv($handle, ['Orders', $summary['orders']]);
            fputcsv($handle, ['Average Order', number_format($summary['average_order'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Order Number', 'Customer', 'Email', 'Date', 'Total', 'Status']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->user?->name ?? $order->billing_full_name,
                    $order->billing_email,
                    $order->paid_at?->format('Y-m-d H:i'),
                    number_format((float) $order->total, 2),
                    $order->status->label(),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
