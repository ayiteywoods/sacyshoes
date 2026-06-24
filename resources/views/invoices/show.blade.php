<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - {{ config('shop.store_name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
            color: #111111;
        }
        .invoice-toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 24px;
            background: #111111;
            color: #ffffff;
        }
        .invoice-toolbar h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        .invoice-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .invoice-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .invoice-btn-primary {
            background: #c41e3a;
            color: #ffffff;
        }
        .invoice-btn-secondary {
            background: #ffffff;
            color: #111111;
        }
        .invoice-page {
            max-width: 900px;
            margin: 24px auto;
            padding: 40px 32px;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        .invoice-page table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-page img {
            max-width: 100%;
            height: auto;
        }
        @media print {
            body { background: #ffffff; }
            .invoice-toolbar { display: none !important; }
            .invoice-page {
                margin: 0;
                padding: 0;
                box-shadow: none;
                max-width: none;
            }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>
    <div class="invoice-toolbar">
        <h1>Invoice #{{ $order->order_number }}</h1>
        <div class="invoice-toolbar-actions">
            <button type="button" class="invoice-btn invoice-btn-primary" onclick="window.print()">Print</button>
            <a href="{{ request()->routeIs('admin.*') ? route('admin.orders.invoice.pdf', $order) : route('orders.invoice.pdf', $order) }}" class="invoice-btn invoice-btn-secondary">Download PDF</a>
            @if (request()->routeIs('admin.*'))
                <a href="{{ route('admin.orders.show', $order) }}" class="invoice-btn invoice-btn-secondary">Back to order</a>
            @elseif (auth()->check() && auth()->id() === $order->user_id)
                <a href="{{ route('account.orders.show', $order) }}" class="invoice-btn invoice-btn-secondary">Back to order</a>
            @endif
        </div>
    </div>

    <div class="invoice-page">
        @include('invoices.partials.content', compact('order', 'currency', 'logoSrc'))
    </div>
</body>
</html>
