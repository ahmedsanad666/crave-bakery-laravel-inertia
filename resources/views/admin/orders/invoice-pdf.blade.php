<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 28px;
        }
        .header {
            width: 100%;
            margin-bottom: 28px;
        }
        .header td { vertical-align: top; }
        .brand-name {
            font-size: 22px;
            font-weight: bold;
            color: #3D1A0E;
            margin: 0 0 8px;
        }
        .brand-meta {
            color: #6B6B6B;
            font-size: 11px;
            line-height: 1.6;
        }
        .logo {
            max-height: 56px;
            max-width: 140px;
            margin-bottom: 8px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #3D1A0E;
            margin: 0 0 8px;
            text-align: right;
        }
        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #84746f;
            margin: 0;
        }
        .meta-value {
            font-size: 13px;
            font-weight: bold;
            margin: 2px 0 0;
        }
        .section {
            border-top: 1px solid #E5DDD4;
            border-bottom: 1px solid #E5DDD4;
            padding: 18px 0;
            margin-bottom: 24px;
            width: 100%;
        }
        .section td { vertical-align: top; width: 50%; }
        h3 {
            font-size: 14px;
            color: #3D1A0E;
            margin: 4px 0 6px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        table.items th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #84746f;
            border-bottom: 1px solid #E5DDD4;
            padding: 8px 4px;
        }
        table.items th.right,
        table.items td.right { text-align: right; }
        table.items th.center,
        table.items td.center { text-align: center; }
        table.items td {
            padding: 12px 4px;
            border-bottom: 1px solid #f0eded;
            vertical-align: top;
        }
        .item-name {
            font-weight: bold;
            color: #3D1A0E;
            margin: 0;
        }
        .item-sub {
            color: #84746f;
            font-size: 10px;
            margin: 2px 0 0;
        }
        .totals {
            width: 280px;
            margin-left: auto;
        }
        .totals td {
            padding: 4px 0;
        }
        .totals td.label { color: #6B6B6B; }
        .totals td.value { text-align: right; }
        .totals .grand td {
            border-top: 1px solid #E5DDD4;
            padding-top: 10px;
            font-size: 16px;
            font-weight: bold;
            color: #3D1A0E;
        }
        .totals .grand td.value { color: #E8572A; }
        .footer {
            margin-top: 36px;
            border-top: 1px solid #E5DDD4;
            padding-top: 18px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #ffdbd1;
            color: #3b0a00;
        }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-failed, .badge-refunded { background: #fee2e2; color: #991b1b; }
        .accent-bar {
            height: 4px;
            background: #E8572A;
            margin: -28px -28px 24px;
        }
    </style>
</head>
<body>
    <div class="accent-bar"></div>

    <table class="header">
        <tr>
            <td>
                @if (! empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="{{ $siteName }}" class="logo">
                @endif
                <p class="brand-name">{{ $siteName }}</p>
                <div class="brand-meta">
                    @if ($siteAddress)
                        {{ $siteAddress }}<br>
                    @endif
                    @if ($siteEmail)
                        {{ $siteEmail }}<br>
                    @endif
                    @if ($sitePhone)
                        {{ $sitePhone }}
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <p class="invoice-title">INVOICE</p>
                <p class="meta-label">Invoice Number</p>
                <p class="meta-value">#{{ $order->order_number }}</p>
            </td>
        </tr>
    </table>

    <table class="section">
        <tr>
            <td>
                <p class="meta-label">Bill To</p>
                <h3>{{ $customerName }}</h3>
                <div class="brand-meta">
                    {{ $order->address_line1 }}
                    @if ($order->address_line2)
                        <br>{{ $order->address_line2 }}
                    @endif
                    <br>
                    {{ $order->city }}@if ($order->state), {{ $order->state }}@endif {{ $order->postal_code }}
                    @if ($order->country)
                        <br>{{ $order->country }}
                    @endif
                    @if ($customerEmail)
                        <br>{{ $customerEmail }}
                    @endif
                </div>
            </td>
            <td style="text-align: right;">
                <p class="meta-label">Date Issued</p>
                <p class="meta-value">{{ $issuedAt }}</p>
                <br>
                <p class="meta-label">Due Date</p>
                <p class="meta-value">{{ $dueAt }}</p>
                <br>
                <p class="meta-label">Payment Method</p>
                <p class="meta-value">{{ $paymentMethodLabel }}</p>
                <br>
                <p class="meta-label">Payment Status</p>
                <p style="margin-top: 4px;">
                    <span class="badge badge-{{ $order->payment_status }}">{{ $paymentStatusLabel }}</span>
                </p>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="center">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->orderItems as $item)
                <tr>
                    <td>
                        <p class="item-name">{{ $item->product_name }}</p>
                        @if ($item->product_sku)
                            <p class="item-sub">SKU: {{ $item->product_sku }}</p>
                        @endif
                    </td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #6B6B6B;">No line items on this order.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">${{ number_format((float) $order->subtotal, 2) }}</td>
        </tr>
        @if ((float) $order->discount_amount > 0)
            <tr>
                <td class="label">
                    @if ($order->promo_code)
                        Promo ({{ $order->promo_code }})
                    @else
                        Discount
                    @endif
                </td>
                <td class="value">-${{ number_format((float) $order->discount_amount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td class="label">Estimated Tax</td>
            <td class="value">${{ number_format((float) $order->tax_amount, 2) }}</td>
        </tr>
        @if ((float) $order->delivery_fee > 0)
            <tr>
                <td class="label">Delivery Fee</td>
                <td class="value">${{ number_format((float) $order->delivery_fee, 2) }}</td>
            </tr>
        @endif
        <tr class="grand">
            <td>Total Amount</td>
            <td class="value">${{ number_format((float) $order->total, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        <p class="meta-label">Notes &amp; Instructions</p>
        <p class="brand-meta" style="font-style: italic;">
            Please settle the invoice within 14 days. For wholesale inquiries or recurring orders, please contact our kitchen directly.
        </p>
        <p style="margin-top: 18px; font-weight: bold; color: #3D1A0E;">Thank You!</p>
        <p class="meta-label" style="margin-top: 8px;">{{ $siteName }}</p>
    </div>
</body>
</html>
