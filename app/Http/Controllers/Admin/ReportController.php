<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminReportService;
use App\Support\AdminTable;
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
        $soldBySize = $reports->paginateQuantityRows(
            $reports->quantitiesSoldBySize($from, $to),
            10,
            'size_page',
        )->withQueryString();
        $soldByColor = $reports->paginateQuantityRows(
            $reports->quantitiesSoldByColor($from, $to),
            10,
            'color_page',
        )->withQueryString();
        $soldByVariant = AdminTable::paginate(
            $reports->quantitiesSoldByVariantQuery($from, $to),
            $request,
            [
                'product_name' => 'product_name',
                'size' => 'size',
                'color' => 'color',
                'units_sold' => 'units_sold',
                'revenue' => 'revenue',
            ],
            'units_sold',
            'desc',
            AdminTable::PER_PAGE,
            'variant_sort',
            'variant_direction',
            'variant_page',
        );
        $soldByVariant->through(fn ($row) => $reports->presentVariantSale($row));
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
            'soldBySize',
            'soldByColor',
            'soldByVariant',
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
        $soldBySize = $reports->quantitiesSoldBySize($from, $to);
        $soldByColor = $reports->quantitiesSoldByColor($from, $to);
        $soldByVariant = $reports->quantitiesSoldByVariantAll($from, $to);

        if ($format === 'pdf') {
            return view('admin.reports.print', compact(
                'from',
                'to',
                'summary',
                'orders',
                'soldBySize',
                'soldByColor',
                'soldByVariant',
            ));
        }

        $filename = 'sacyshoes-sales-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($orders, $summary, $from, $to, $soldBySize, $soldByColor, $soldByVariant) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Sacy Shoes Sales Report']);
            fputcsv($handle, ['Period', $from->format('M j, Y').' - '.$to->format('M j, Y')]);
            fputcsv($handle, ['Revenue', number_format($summary['revenue'], 2)]);
            fputcsv($handle, ['Orders', $summary['orders']]);
            fputcsv($handle, ['Units Sold', $summary['units_sold']]);
            fputcsv($handle, ['Average Order', number_format($summary['average_order'], 2)]);
            fputcsv($handle, []);
            fputcsv($handle, ['Quantities sold by size']);
            fputcsv($handle, ['Size', 'Units sold', 'In stock']);

            foreach ($soldBySize as $row) {
                fputcsv($handle, [$row->label, $row->units_sold, $row->stock_left]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Quantities sold by color']);
            fputcsv($handle, ['Color', 'Units sold', 'In stock']);

            foreach ($soldByColor as $row) {
                fputcsv($handle, [$row->label, $row->units_sold, $row->stock_left]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Quantities sold by product, size and color']);
            fputcsv($handle, ['Product', 'Size', 'Color', 'Units sold', 'In stock', 'Revenue']);

            foreach ($soldByVariant as $row) {
                fputcsv($handle, [
                    $row->product_name,
                    $row->size,
                    $row->color,
                    $row->units_sold,
                    $row->stock_left,
                    number_format($row->revenue, 2),
                ]);
            }

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
