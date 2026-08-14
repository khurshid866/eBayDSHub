@extends('layouts.app')

@section('title', 'Reports & Analytics Hub')

@section('content')
<div class="space-y-6">

    <!-- Header & Export Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Financial & Sales Analytics Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Generate filtered report statements and export as Excel, PDF, or CSV.</p>
        </div>

        @if(Auth::user()?->hasPermission('action_export_reports'))
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.excel', request()->all()) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 border border-slate-700 font-bold text-xs rounded-xl transition flex items-center space-x-2">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export Excel</span>
            </a>
            <a href="{{ route('reports.pdf', request()->all()) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-purple-400 border border-slate-700 font-bold text-xs rounded-xl transition flex items-center space-x-2">
                <i class="fa-solid fa-file-pdf"></i>
                <span>Export PDF</span>
            </a>
            <a href="{{ route('reports.csv', request()->all()) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-blue-400 border border-slate-700 font-bold text-xs rounded-xl transition flex items-center space-x-2">
                <i class="fa-solid fa-file-csv"></i>
                <span>Export CSV</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Aggregate Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Filtered Orders</span>
            <p class="text-xl font-extrabold text-white mt-1">{{ number_format($summary['total_orders']) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Customer Revenue</span>
            <p class="text-xl font-extrabold text-emerald-400 mt-1">{{ $currencySymbol }}{{ number_format($summary['customer_revenue'], 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Ad Fees</span>
            <p class="text-xl font-extrabold text-rose-400 mt-1">{{ $currencySymbol }}{{ number_format($summary['ad_fee_charges'], 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Supplier Cost</span>
            <p class="text-xl font-extrabold text-indigo-400 mt-1">{{ $currencySymbol }}{{ number_format($summary['supplier_cost'], 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Net Profit</span>
            <p class="text-xl font-extrabold text-blue-400 mt-1">{{ $currencySymbol }}{{ number_format($summary['total_profit'], 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Avg Weighted ROI</span>
            <p class="text-xl font-extrabold text-purple-400 mt-1">{{ number_format($summary['avg_roi'] * 100, 2) }}%</p>
        </div>
    </div>

    <!-- Report Filters Form -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            
            <!-- From Date -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">From Date</label>
                <div class="relative">
                    <input type="date" name="from_date" id="rep_from_date" value="{{ request('from_date') }}" class="w-full pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- To Date -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">To Date</label>
                <div class="relative">
                    <input type="date" name="to_date" id="rep_to_date" value="{{ request('to_date') }}" class="w-full pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
                    <option value="all">All Statuses</option>
                    @foreach(['Pending', 'Purchased', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Refunded'] as $st)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Min Profit -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Min Profit ({{ $currencySymbol }})</label>
                <input type="number" step="0.01" name="min_profit" value="{{ request('min_profit') }}" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
            </div>

            <!-- Max Profit -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Max Profit ({{ $currencySymbol }})</label>
                <input type="number" step="0.01" name="max_profit" value="{{ request('max_profit') }}" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
            </div>

            <!-- Min ROI -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Min ROI (%)</label>
                <input type="number" step="0.1" name="min_roi" value="{{ request('min_roi') }}" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition">
                    Apply
                </button>
                <a href="{{ route('reports.index') }}" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg text-xs transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Report Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">eBay Order</th>
                        <th class="py-3 px-4 text-right">Customer Price</th>
                        <th class="py-3 px-4 text-right">Ad Fee</th>
                        <th class="py-3 px-4">Amazon Order</th>
                        <th class="py-3 px-4 text-right">Supplier Cost</th>
                        <th class="py-3 px-4 text-right">E_NET</th>
                        <th class="py-3 px-4 text-right">Profit</th>
                        <th class="py-3 px-4 text-right">ROI (%)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">{{ $order->order_date ? $order->order_date->format('Y-m-d') : '-' }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-white whitespace-nowrap">{{ $order->ebay_order_number }}</td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">{{ $currencySymbol }}{{ number_format($order->customer_price, 2) }}</td>
                        <td class="py-3 px-4 text-right text-rose-400 font-semibold whitespace-nowrap">{{ $currencySymbol }}{{ number_format($order->ad_fee_charges, 2) }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">{{ $order->amazon_order_number ?: '-' }}</td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">{{ $currencySymbol }}{{ number_format($order->supplier_cost, 2) }}</td>
                        <td class="py-3 px-4 text-right text-slate-200 font-bold whitespace-nowrap">{{ $currencySymbol }}{{ number_format($order->ebay_net, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap {{ $order->profit > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $currencySymbol }}{{ number_format($order->profit, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-blue-400 whitespace-nowrap">
                            {{ number_format($order->roi * 100, 2) }}%
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                {{ $order->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-500">No records found matching report criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
