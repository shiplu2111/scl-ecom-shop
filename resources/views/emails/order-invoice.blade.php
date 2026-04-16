<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice – #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            color: #334155;
            line-height: 1.6;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        .invoice-container {
            max-width: 1000px;
            margin: 10px auto;
            background: #ffffff;
            padding: 25px;
        }

        /* Header Section */
        .header-table {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .brand h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
        }

        .brand p {
            margin: 4px 0;
            font-size: 13px;
            color: #64748b;
        }

        .invoice-meta h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            color: #4f46e5;
        }

        .invoice-meta p {
            margin: 4px 0;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        /* Information Grid */
        .info-table {
            margin-bottom: 20px;
        }

        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .info-block p {
            margin: 0;
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }

        /* Items Table */
        .items-table thead th {
            background-color: #f8fafc;
            text-align: left;
            padding: 8px 15px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        .items-table tbody td {
            padding-top: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

         
        .items-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        /* Even rows (2, 4, 6...) - Light Gray/Blue background */
        .items-table tbody tr:nth-child(even) {
            background-color: #f1f5f9; /* Apni chaile ekhane light blue #f1f5f9 o dite paren */
        }

        .product-name {
            font-weight: 700;
            color: #0f172a;
            display: block;
        }

        .product-desc {
            font-size: 11px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }

        /* Totals */
        .summary-table {
            width: 320px;
            margin-left: auto;
            margin-right: 0;
        }

        .summary-table td {
            padding: 4px 0;
            font-size: 14px;
            vertical-align: middle;
        }

        .summary-table td:last-child {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }

        .grand-total td {
            border-top: 2px solid #f1f5f9;
            padding-top: 10px !important;
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #4f46e5 !important;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 700;
            background: #dcfce7;
            color: #15803d;
            text-transform: uppercase;
            margin-top: 8px;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0;
        }

        .currency {
            font-family: DejaVu Sans, sans-serif !important;
        }
    </style>
</head>
<body>

@php
    $cur = ($site_settings['currency_code'] ?? 'BDT') . ' ';
@endphp

<div class="invoice-container">
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td width="60%" class="brand">
                @if(!empty($site_settings['site_logo']))
                    <img src="{{ $site_settings['site_logo'] }}" style="height: 60px; margin-bottom: 15px;"><br>
                @endif
                <h1>{{ $site_settings['site_name'] }}</h1>
                <p>
                    @if(!empty($site_settings['site_address']))
                        {!! nl2br(e($site_settings['site_address'])) !!}<br>
                    @endif
                    {{ $site_settings['site_email'] }}
                </p>
            </td>
            <td width="40%" class="invoice-meta" align="right">
                <h2>INVOICE</h2>
                <p>No: #{{ $order->order_number }}</p>
                <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
                <div class="status-badge" style="{{ $order->payment_status === 'paid' ? 'background: #dcfce7; color: #15803d;' : 'background: #fee2e2; color: #b91c1c;' }}">
                    {{ strtoupper($order->payment_status) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Info Grid -->
    <table class="info-table">
        <tr>
            <td width="50%" class="info-block">
                <h3>Billed To</h3>
                <p><strong>{{ $order->user?->name ?? 'Valued Customer' }}</strong></p>
                <p>{{ $order->user?->email }}</p>
                @if($order->shippingAddress)
                    <p>
                        {{ $order->shippingAddress->address_line }}<br>
                        {{ $order->shippingAddress->thana?->name }}, {{ $order->shippingAddress->district?->name }}<br>
                        {{ $order->shippingAddress->division?->name }} @if($order->shippingAddress->postal_code) – {{ $order->shippingAddress->postal_code }} @endif<br>
                        Phone: {{ $order->shippingAddress->phone }}
                    </p>
                @endif
            </td>
            <td width="50%" class="info-block" align="right">
                <h3>Payment Details</h3>
                <p>Method: {{ strtoupper($order->payment_method) }}</p>
                @if($order->payment_id)
                    <p>Transaction ID: {{ $order->payment_id }}</p>
                @endif
                <p>Status: {{ ucfirst($order->payment_status) }}</p>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th width="40%">Description</th>
                <th width="25%" align="right">Price</th>
                <th width="10%" align="center">Qty</th>
                <th width="25%" align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <span class="product-name">{{ $item->product?->name ?? 'Premium Product' }}</span>
                    @if($item->productVariant)
                        <span class="product-desc">Variant: {{ $item->productVariant->name }}</span>
                    @endif
                </td>
                <td align="right" style="text-align: right; white-space: nowrap;">{{ number_format($item->unit_price, 2) }} {{ $cur }}</td>
                <td align="center" style="text-align: center;">{{ $item->quantity }}</td>
                <td align="right" style="text-align: right; white-space: nowrap;">{{ number_format($item->total_price, 2) }} {{ $cur }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td width="55%"></td>
            <td width="45%">
                <table class="summary-table" >
                    <tr>
                        <td class="info-block" style="text-align: left;">Subtotal</td>
                        <td style="white-space: nowrap; text-align: right;">{{ number_format($order->subtotal, 2) }} {{ $cur }}</td>
                    </tr>
                    @if($order->delivery_charge > 0)
                    <tr>
                        <td class="info-block" style="text-align: left;">Shipping</td>
                        <td style="white-space: nowrap; text-align: right;">{{ number_format($order->delivery_charge, 2) }} {{ $cur }}</td>
                    </tr>
                    @endif
                    @if($order->discount_amount > 0)
                    <tr>
                        <td class="info-block" style="text-align: left; color: #10b981;">Discount</td>
                        <td style="color: #10b981; white-space: nowrap; text-align: right;">- {{ number_format($order->discount_amount, 2) }} {{ $cur }}</td>
                    </tr>
                    @endif
                    <tr class="grand-total">
                        <td style="text-align: left;">Total Amount</td>
                        <td style="white-space: nowrap; text-align: right;">{{ number_format($order->grand_total, 2) }}  {{ $cur }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Thank you for choosing {{ $site_settings['site_name'] }}!</strong></p>
        <p>This is a computer-generated invoice and does not require a physical signature.</p>
        <p>© {{ date('Y') }} {{ $site_settings['site_name'] }}. All Rights Reserved.</p>
    </div>
</div>

</body>
</html>