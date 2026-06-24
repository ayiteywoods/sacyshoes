<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        @page { margin: 14mm; }
        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            color: #111111;
            font-size: 12px;
        }
        table { border-collapse: collapse; }
    </style>
</head>
<body>
    @include('invoices.partials.content', compact('order', 'currency', 'logoSrc'))
</body>
</html>
