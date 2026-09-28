<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sales Report - {{ $from->format('Y-m-d') }} to {{ $to->format('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; margin: 2rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .muted { color: #666; font-size: 0.9rem; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 1.5rem 0; }
        h2 { font-size: 1.1rem; margin: 2rem 0 0; }
        .stat { border: 1px solid #ddd; padding: 1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
        th, td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
        th { background: #f5f5f5; }
        .actions { margin-bottom: 1rem; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    <h1>Sacy Shoes Sales Report</h1>
    <p class="muted">{{ $from->format('M j, Y') }} – {{ $to->format('M j, Y') }}</p>

    <div class="stats">
        <div class="stat">
            <div class="muted">Revenue</div>
            <strong>GHS {{ number_format($summary['revenue'], 2) }}</strong>
        </div>
        <div class="stat">
            <div class="muted">Orders</div>
            <strong>{{ $summary['orders'] }}</strong>
        </div>
        <div class="stat">
            <div class="muted">Average Order</div>
            <strong>GHS {{ number_format($summary['average_order'], 2) }}</strong>
        </div>
        <div class="stat">
            <div class="muted">Pairs sold</div>
            <strong>{{ number_format($summary['units_sold']) }}</strong>
        </div>
    </div>

    <h2>Quantities sold by size</h2>
    <table>
        <thead>
            <tr>
                <th>Size</th>
                <th>Units sold</th>
                <th>In stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($soldBySize as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td>{{ number_format($row->units_sold) }}</td>
                    <td>{{ number_format($row->stock_left) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No paid sales in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Quantities sold by color</h2>
    <table>
        <thead>
            <tr>
                <th>Color</th>
                <th>Units sold</th>
                <th>In stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($soldByColor as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td>{{ number_format($row->units_sold) }}</td>
                    <td>{{ number_format($row->stock_left) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No paid sales in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Sold by size and color</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Color</th>
                <th>Qty sold</th>
                <th>In stock</th>
                <th>Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($soldByVariant as $row)
                <tr>
                    <td>{{ $row->product_name }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ number_format($row->units_sold) }}</td>
                    <td>{{ number_format($row->stock_left) }}</td>
                    <td>GHS {{ number_format($row->revenue, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">No paid sales in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->user?->name ?? $order->billing_full_name }}</td>
                    <td>{{ $order->paid_at?->format('M j, Y g:i A') }}</td>
                    <td>GHS {{ number_format($order->total, 2) }}</td>
                    <td>{{ $order->status->label() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No paid orders in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
