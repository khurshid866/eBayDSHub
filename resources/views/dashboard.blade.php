@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Top Date Filter Controls Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl flex flex-col lg:flex-row items-center justify-between gap-4 shadow-xl">
        <div class="flex items-center space-x-2 overflow-x-auto w-full lg:w-auto pb-2 lg:pb-0">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 shrink-0">Filter:</span>
            
            @php
                $presets = [
                    'all' => 'All Time',
                    'today' => 'Today',
                    'yesterday' => 'Yesterday',
                    'this_week' => 'This Week',
                    'this_month' => 'This Month',
                    'last_month' => 'Last Month',
                    'this_year' => 'This Year',
                ];
            @endphp

            @foreach($presets as $key => $label)
                <a href="{{ route('dashboard', ['preset' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold shrink-0 transition {{ $preset === $key ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'bg-slate-800/80 text-slate-400 hover:text-white hover:bg-slate-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="flex items-center space-x-2 w-full lg:w-auto">
            <input type="hidden" name="preset" value="custom">
            <div class="relative">
                <input type="date" name="from_date" value="{{ $fromDate }}" class="pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                </span>
            </div>
            <span class="text-slate-500 text-xs">to</span>
            <div class="relative">
                <input type="date" name="to_date" value="{{ $toDate }}" class="pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                </span>
            </div>
            <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-semibold transition">
                Apply
            </button>
        </form>
    </div>

    <!-- Executive KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Orders -->
        <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Orders</span>
                    <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($stats['total_orders']) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-boxes-stack"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs">
                <span class="text-emerald-400 font-semibold"><i class="fa-solid fa-circle-check"></i> {{ $stats['completed_orders'] }} Completed</span>
                <span class="text-amber-400 font-semibold"><i class="fa-solid fa-clock"></i> {{ $stats['pending_orders'] }} Pending</span>
            </div>
        </div>

        <!-- Customer Revenue -->
        <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Customer Revenue</span>
                    <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">{{ $currencySymbol }}{{ number_format($stats['customer_revenue'], 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 text-xs text-slate-400">
                E_NET: <span class="text-white font-bold">{{ $currencySymbol }}{{ number_format($stats['total_ebay_net'], 2) }}</span>
            </div>
        </div>

        <!-- Supplier Cost -->
        <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Supplier Cost</span>
                    <h3 class="text-2xl font-extrabold text-indigo-400 mt-1">{{ $currencySymbol }}{{ number_format($stats['supplier_cost'], 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 text-xs text-slate-400">
                Amazon Purchases
            </div>
        </div>

        <!-- Total Profit & ROI -->
        <div class="bg-slate-900/80 border border-slate-800 p-5 rounded-2xl shadow-xl relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Net Profit</span>
                    <h3 class="text-2xl font-extrabold text-blue-400 mt-1">{{ $currencySymbol }}{{ number_format($stats['total_profit'], 2) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-800/60 flex items-center justify-between text-xs">
                <span class="text-slate-400">Avg Weighted ROI:</span>
                <span class="font-extrabold text-blue-400 px-2 py-0.5 rounded bg-blue-500/10 border border-blue-500/20">
                    {{ number_format($stats['average_roi'] * 100, 2) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Business Insights Banner Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 p-4 rounded-xl flex items-center space-x-4">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-trophy text-lg"></i>
            </div>
            <div class="truncate">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Best Performing Day</span>
                <p class="text-sm font-extrabold text-white truncate">
                    {{ $stats['insights']['best_performing_day']['date'] ?? 'N/A' }} 
                    <span class="text-emerald-400">({{ $currencySymbol }}{{ number_format($stats['insights']['best_performing_day']['profit'] ?? 0, 2) }})</span>
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 p-4 rounded-xl flex items-center space-x-4">
            <div class="w-10 h-10 rounded-lg bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-arrow-trend-up text-lg"></i>
            </div>
            <div class="truncate">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">MoM Profit Change</span>
                <p class="text-sm font-extrabold text-white truncate">
                    <span class="{{ $stats['insights']['mom_profit_change'] >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ number_format($stats['insights']['mom_profit_change'], 1) }}%
                    </span>
                    <span class="text-xs text-slate-400 font-normal">vs last month</span>
                </p>
            </div>
        </div>

        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800 p-4 rounded-xl flex items-center space-x-4">
            <div class="w-10 h-10 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="truncate">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Low ROI Alerts (&lt;10%)</span>
                <p class="text-sm font-extrabold text-rose-400 truncate">
                    {{ $stats['insights']['low_roi_orders_count'] }} Order(s)
                </p>
            </div>
        </div>
    </div>

    <!-- Visual Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Profit by Date Chart -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-chart-line text-blue-400"></i>
                    <span>Net Profit Over Time</span>
                </h4>
            </div>
            <div class="h-64">
                <canvas id="profitChart"></canvas>
            </div>
        </div>

        <!-- Revenue vs Supplier Cost Chart -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-chart-column text-emerald-400"></i>
                    <span>Revenue vs Supplier Cost</span>
                </h4>
            </div>
            <div class="h-64">
                <canvas id="revenueCostChart"></canvas>
            </div>
        </div>

        <!-- ROI Trend Chart -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-percent text-purple-400"></i>
                    <span>ROI % Trend</span>
                </h4>
            </div>
            <div class="h-64">
                <canvas id="roiTrendChart"></canvas>
            </div>
        </div>

        <!-- Orders by Status Chart -->
        <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h4 class="font-bold text-white text-sm flex items-center space-x-2">
                    <i class="fa-solid fa-chart-pie text-indigo-400"></i>
                    <span>Orders by Status</span>
                </h4>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Tables: Top Profitable Orders & Low ROI Orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Top 10 Most Profitable Orders -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h4 class="font-bold text-white text-sm mb-4 flex items-center justify-between">
                <span class="flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-up-right-dots text-emerald-400"></i>
                    <span>Top 10 Most Profitable Orders</span>
                </span>
                <a href="{{ route('orders.index', ['sort' => 'profit', 'direction' => 'desc']) }}" class="text-xs text-blue-400 hover:underline">View All</a>
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">eBay Order</th>
                            <th class="py-2.5 px-3 text-right">Profit</th>
                            <th class="py-2.5 px-3 text-right">ROI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($topOrders as $ord)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3 text-slate-400">{{ $ord->order_date ? $ord->order_date->format('M d, Y') : '-' }}</td>
                            <td class="py-2.5 px-3 font-mono text-white">
                                <a href="{{ route('orders.show', $ord) }}" class="hover:text-blue-400">{{ $ord->ebay_order_number }}</a>
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold text-emerald-400">{{ $currencySymbol }}{{ number_format($ord->profit, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-blue-400">{{ number_format($ord->roi * 100, 2) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low ROI Orders Alert -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
            <h4 class="font-bold text-white text-sm mb-4 flex items-center justify-between">
                <span class="flex items-center space-x-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                    <span>Low ROI Orders (&lt;10%)</span>
                </span>
                <a href="{{ route('orders.index', ['max_roi' => 10]) }}" class="text-xs text-rose-400 hover:underline">View All</a>
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400">
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-3">eBay Order</th>
                            <th class="py-2.5 px-3 text-right">Profit</th>
                            <th class="py-2.5 px-3 text-right">ROI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($lowRoiOrders as $ord)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-3 text-slate-400">{{ $ord->order_date ? $ord->order_date->format('M d, Y') : '-' }}</td>
                            <td class="py-2.5 px-3 font-mono text-white">
                                <a href="{{ route('orders.show', $ord) }}" class="hover:text-blue-400">{{ $ord->ebay_order_number }}</a>
                            </td>
                            <td class="py-2.5 px-3 text-right font-bold {{ $ord->profit <= 0 ? 'text-rose-400' : 'text-slate-300' }}">{{ $currencySymbol }}{{ number_format($ord->profit, 2) }}</td>
                            <td class="py-2.5 px-3 text-right font-bold text-rose-400">{{ number_format($ord->roi * 100, 2) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-slate-500">No low ROI orders detected</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartColors = {
            blue: '#3b82f6',
            emerald: '#10b981',
            indigo: '#6366f1',
            purple: '#a855f7',
            amber: '#f59e0b',
            rose: '#f43f5e'
        };

        // Profit Chart
        const profitCtx = document.getElementById('profitChart').getContext('2d');
        new Chart(profitCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['profit_by_date']['labels']) !!},
                datasets: [{
                    label: 'Net Profit ({{ $currencySymbol }})',
                    data: {!! json_encode($chartData['profit_by_date']['profits']) !!},
                    borderColor: chartColors.blue,
                    backgroundColor: 'rgba(59, 130, 246, 0.15)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } }
                }
            }
        });

        // Revenue vs Cost Chart
        const revCtx = document.getElementById('revenueCostChart').getContext('2d');
        new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartData['profit_by_date']['labels']) !!},
                datasets: [
                    {
                        label: 'Customer Revenue',
                        data: {!! json_encode($chartData['profit_by_date']['revenues']) !!},
                        backgroundColor: chartColors.emerald,
                        borderRadius: 4
                    },
                    {
                        label: 'Supplier Cost',
                        data: {!! json_encode($chartData['profit_by_date']['costs']) !!},
                        backgroundColor: chartColors.indigo,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } }
                }
            }
        });

        // ROI Trend Chart
        const roiCtx = document.getElementById('roiTrendChart').getContext('2d');
        new Chart(roiCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['roi_trend']['labels']) !!},
                datasets: [{
                    label: 'Avg ROI %',
                    data: {!! json_encode($chartData['roi_trend']['values']) !!},
                    borderColor: chartColors.purple,
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } },
                    y: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($chartData['orders_by_status']['labels']) !!},
                datasets: [{
                    data: {!! json_encode($chartData['orders_by_status']['values']) !!},
                    backgroundColor: [chartColors.emerald, chartColors.amber, chartColors.blue, chartColors.rose, chartColors.purple]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { color: '#94a3b8' } } }
            }
        });
    });
</script>
@endpush
