@php
    $currency = $currency ?? config('shop.currency_symbol', '₵');
    $logoSrc = $logoSrc ?? null;
    $invoicePhones = \App\Models\StoreSetting::current()->invoiceContactPhones();
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;max-width:760px;border-collapse:collapse;margin:0 auto;table-layout:fixed;">
    <colgroup>
        <col style="width:33%;">
        <col style="width:33%;">
        <col style="width:34%;">
    </colgroup>

    <tr>
        <td style="padding-bottom:28px;vertical-align:top;">
            @if ($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ config('shop.store_name') }}" width="72" style="display:block;border:0;max-width:72px;height:auto;">
            @endif
        </td>
        <td colspan="2" style="padding-bottom:28px;vertical-align:top;text-align:right;font-size:13px;line-height:1.6;color:#111111;">
            <div style="font-weight:700;font-size:15px;">{{ config('shop.store_name') }}</div>
            <div>{{ config('shop.contact_address') }}</div>
            <div>{{ implode(' / ', $invoicePhones) }}</div>
            <div>{{ config('shop.contact_email') }}</div>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding:8px 0 24px;font-size:34px;font-weight:700;letter-spacing:0.02em;">
            INVOICE
        </td>
    </tr>

    <tr>
        <td style="padding:0 16px 24px 0;vertical-align:top;font-size:13px;line-height:1.7;word-wrap:break-word;">
            <div style="font-weight:700;margin-bottom:6px;">Bill To:</div>
            <div style="font-weight:700;">{{ $order->billing_full_name }}</div>
            <div>{{ $order->billing_address }}</div>
            <div>{{ $order->billing_city }}, {{ $order->billing_country }}</div>
            <div style="margin-top:8px;">{{ $order->billing_email }}</div>
            <div>{{ $order->billing_phone }}</div>
        </td>
        <td style="padding:0 8px 24px;vertical-align:top;font-size:13px;line-height:1.7;word-wrap:break-word;">
            <div style="font-weight:700;margin-bottom:6px;">Ship To:</div>
            <div>{{ $order->shipping_full_name }}</div>
            <div>{{ $order->shipping_address }}</div>
            <div>{{ $order->shipping_city }}, {{ $order->shipping_country }}</div>
            <div style="margin-top:8px;">{{ $order->shipping_phone }}</div>
        </td>
        <td style="padding:0 0 24px 16px;vertical-align:top;font-size:13px;line-height:1.8;word-wrap:break-word;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;line-height:1.8;">
                <tr>
                    <td style="padding:2px 12px 2px 0;vertical-align:top;white-space:nowrap;font-weight:700;">Invoice Number:</td>
                    <td style="padding:2px 0;vertical-align:top;">{{ $order->invoiceNumber() }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 12px 2px 0;vertical-align:top;white-space:nowrap;font-weight:700;">Invoice Date:</td>
                    <td style="padding:2px 0;vertical-align:top;">{{ $order->invoiceDate()->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 12px 2px 0;vertical-align:top;white-space:nowrap;font-weight:700;">Order Number:</td>
                    <td style="padding:2px 0;vertical-align:top;">{{ $order->order_number }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 12px 2px 0;vertical-align:top;white-space:nowrap;font-weight:700;">Order Date:</td>
                    <td style="padding:2px 0;vertical-align:top;">{{ $order->created_at->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 12px 2px 0;vertical-align:top;white-space:nowrap;font-weight:700;">Payment Method:</td>
                    <td style="padding:2px 0;vertical-align:top;">{{ $order->paymentMethodLabel() }}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:13px;table-layout:fixed;">
                <colgroup>
                    <col>
                    <col style="width:90px;">
                    <col style="width:110px;">
                </colgroup>
                <tr>
                    <th align="left" style="padding:12px 14px;font-weight:700;color:#111111;border-bottom:1px solid #111111;">Product</th>
                    <th align="center" style="padding:12px 14px;font-weight:700;color:#111111;border-bottom:1px solid #111111;">Quantity</th>
                    <th align="right" style="padding:12px 14px;font-weight:700;color:#111111;border-bottom:1px solid #111111;">Price</th>
                </tr>
                @foreach ($order->items as $item)
                    <tr>
                        <td style="padding:14px;border-bottom:1px solid #e5e5e5;vertical-align:top;line-height:1.6;word-wrap:break-word;">
                            <div style="font-weight:700;">{{ $item->invoiceProductTitle() }}</div>
                            @foreach ($item->invoiceVariantLines() as $line)
                                <div style="color:#444444;">{{ $line }}</div>
                            @endforeach
                        </td>
                        <td align="center" style="padding:14px;border-bottom:1px solid #e5e5e5;vertical-align:top;">{{ $item->quantity }}</td>
                        <td align="right" style="padding:14px;border-bottom:1px solid #e5e5e5;vertical-align:top;white-space:nowrap;">{{ $currency }}{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding-top:18px;">
            <table role="presentation" align="right" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;min-width:320px;max-width:100%;">
                <tr>
                    <td style="padding:6px 24px 6px 0;text-align:right;">Subtotal</td>
                    <td style="padding:6px 0;text-align:right;white-space:nowrap;">{{ $currency }}{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 24px 6px 0;text-align:right;vertical-align:top;">Shipping</td>
                    <td style="padding:6px 0;text-align:right;line-height:1.5;max-width:280px;">{{ $order->invoiceShippingLabel() }}</td>
                </tr>
                @if ((float) $order->tax > 0)
                    <tr>
                        <td style="padding:6px 24px 6px 0;text-align:right;">Tax</td>
                        <td style="padding:6px 0;text-align:right;white-space:nowrap;">{{ $currency }}{{ number_format($order->tax, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td colspan="2" style="padding-top:10px;">
                        <div style="border-top:1px solid #111111;margin-bottom:10px;"></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:6px 24px 6px 0;text-align:right;font-weight:700;">Total</td>
                    <td style="padding:6px 0;text-align:right;font-weight:700;white-space:nowrap;">{{ $currency }}{{ number_format($order->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:10px;">
                        <div style="border-top:1px solid #111111;"></div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>

    <tr>
        <td colspan="3" style="padding-top:36px;font-size:12px;line-height:1.6;color:#666666;text-align:center;">
            Thank you for shopping with {{ config('shop.store_name') }}.
            @if (config('shop.contact_phone'))
                <br>Questions? Call {{ config('shop.contact_phone') }} or email {{ config('shop.contact_email') }}.
            @endif
        </td>
    </tr>
</table>
