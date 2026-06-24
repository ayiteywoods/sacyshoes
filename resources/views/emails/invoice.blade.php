<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background-color:#ffffff;font-family:Arial,Helvetica,sans-serif;color:#111111;">
@php
    $currency = $currency ?? config('shop.currency_symbol', '₵');
    $logoSrc = $logoSrc ?? app(\App\Services\InvoiceService::class)->logoDataUri();
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            @include('invoices.partials.content', compact('order', 'currency', 'logoSrc'))

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:760px;margin:28px auto 0;border-collapse:collapse;">
                <tr>
                    <td align="center" style="font-size:14px;line-height:1.6;">
                        <a href="{{ \App\Support\OrderMailUrls::invoice($order) }}" style="color:#c41e3a;font-weight:700;text-decoration:none;">View &amp; Print Invoice</a>
                        &nbsp;&nbsp;|&nbsp;&nbsp;
                        <a href="{{ \App\Support\OrderMailUrls::invoicePdf($order) }}" style="color:#c41e3a;font-weight:700;text-decoration:none;">Download PDF</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
