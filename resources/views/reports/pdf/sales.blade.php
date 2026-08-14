<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>eBay Dropshipping Order Report</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; margin: 0; padding: 20px; }
        .header { margin-bottom: 20px; border-b: 2px solid #2563eb; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 3px 0 0 0; color: #64748b; font-size: 9px; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 20px; }
        .summary-grid { display: table; width: 100%; }
        .summary-col { display: table-cell; width: 14.28%; text-align: center; border-right: 1px solid #e2e8f0; }
        .summary-col:last-child { border-right: none; }
        .summary-label { font-size: 8px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0f172a; color: #ffffff; text-align: left; padding: 6px 8px; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px 8px; font-size: 9px; }
        tr:nth-child(even) { background-row: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: Courier, monospace; font-weight: bold; }
        .text-emerald { color: #059669; font-weight: bold; }
        .text-rose { color: #dc2626; font-weight: bold; }
        .footer { margin-top: 30px; border-t: 1px solid #e2e8f0; pt: 10px; text-align: justify; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>

    <div class="header">
        <h1>eBay Dropshipping Order Report</h1>
        <p>Generated on {{ date('F d, Y \a\t H:i:s') }} | Total Records: {{ number_format($summary['total_orders']) }}</p>
    </div>

    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-col">
                <div class="summary-label">Total Orders</div>
                <div class="summary-val">{{ number_format($summary['total_orders']) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Revenue</div>
                <div class="summary-val">{{ $currencySymbol }}{{ number_format($summary['customer_revenue'], 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Ad Fees</div>
                <div class="summary-val text-rose">{{ $currencySymbol }}{{ number_format($summary['ad_fee_charges'], 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Supplier Cost</div>
                <div class="summary-val">{{ $currencySymbol }}{{ number_format($summary['supplier_cost'], 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">E_NET</div>
                <div class="summary-val">{{ $currencySymbol }}{{ number_format($summary['ebay_net'], 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Net Profit</div>
                <div class="summary-val text-emerald">{{ $currencySymbol }}{{ number_format($summary['total_profit'], 2) }}</div>
            </div>
            <div class="summary-col">
                <div class="summary-label">Avg ROI</div>
                <div class="summary-val">{{ number_format($summary['avg_roi'] * 100, 2) }}%</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>eBay Order Number</th>
                <th class="text-right">Cust Price</th>
                <th class="text-right">Ad Fee</th>
                <th>Amazon Order Number</th>
                <th class="text-right">Supplier Cost</th>
                <th class="text-right">E_NET</th>
                <th class="text-right">Profit</th>
                <th class="text-right">ROI (%)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->order_date ? $order->order_date->format('Y-m-d') : '-' }}</td>
                <td class="font-mono">{{ $order->ebay_order_number }}</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($order->customer_price, 2) }}</td>
                <td class="text-right text-rose">{{ $currencySymbol }}{{ number_format($order->ad_fee_charges, 2) }}</td>
                <td class="font-mono">{{ $order->amazon_order_number ?: '-' }}</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($order->supplier_cost, 2) }}</td>
                <td class="text-right">{{ $currencySymbol }}{{ number_format($order->ebay_net, 2) }}</td>
                <td class="text-right {{ $order->profit > 0 ? 'text-emerald' : 'text-rose' }}">
                    {{ $currencySymbol }}{{ number_format($order->profit, 2) }}
                </td>
                <td class="text-right">{{ number_format($order->roi * 100, 2) }}%</td>
                <td class="text-center">{{ $order->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential Report - Generated by eBay Dropshipping Hub Management System.
    </div>

</body>
</html>
