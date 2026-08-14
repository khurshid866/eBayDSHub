@extends('layouts.app')

@section('title', 'Orders Management')

@section('content')
<div class="space-y-6" x-data="{ selectedIds: [], selectAll: false }">

    <!-- Header Actions & Search/Filter Panel -->
    <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl space-y-4">
        
        <!-- Search & Action Buttons Row -->
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            
            <!-- Global Search Box -->
            <form method="GET" action="{{ route('orders.index') }}" class="w-full md:w-96 flex items-center space-x-2">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search order #, Amazon # or notes..."
                           class="w-full pl-9 pr-4 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition">
                    Search
                </button>
            </form>

            <div class="flex items-center space-x-3 w-full md:w-auto justify-end">
                @if(Auth::user()?->hasPermission('nav_import'))
                <a href="{{ route('import.template') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 hover:text-white text-xs font-semibold rounded-xl border border-slate-700/60 transition flex items-center space-x-2" title="Download Sample Excel Template">
                    <i class="fa-solid fa-download"></i>
                    <span>Sample Excel</span>
                </a>
                <a href="{{ route('import.index') }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition flex items-center space-x-2">
                    <i class="fa-solid fa-file-excel text-emerald-400"></i>
                    <span>Import Excel</span>
                </a>
                @endif

                @if(Auth::user()?->hasPermission('action_create_order'))
                <a href="{{ route('orders.create') }}" class="px-3.5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Order</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Filter Controls Form -->
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-3 border-t border-slate-800/80">
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

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

            <!-- Date From -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">From Date</label>
                <div class="relative">
                    <input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="w-full pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">To Date</label>
                <div class="relative">
                    <input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="w-full pl-8 pr-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white focus:border-blue-500">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-blue-400">
                        <i class="fa-solid fa-calendar-days text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- Min Profit -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Min Profit ({{ $currencySymbol }})</label>
                <input type="number" step="0.01" name="min_profit" value="{{ request('min_profit') }}" placeholder="0.00" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
            </div>

            <!-- Min ROI -->
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Min ROI (%)</label>
                <input type="number" step="0.1" name="min_roi" value="{{ request('min_roi') }}" placeholder="10%" class="w-full px-2.5 py-1.5 bg-slate-950 border border-slate-800 rounded-lg text-xs text-white">
            </div>

            <!-- Filter Trigger / Reset -->
            <div class="flex items-end space-x-2">
                <button type="submit" class="w-full py-1.5 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-semibold transition">
                    Filter
                </button>
                <a href="{{ route('orders.index') }}" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-lg text-xs transition" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar -->
    @if(Auth::user()?->hasPermission('action_bulk_orders'))
    <div x-show="selectedIds.length > 0" x-cloak class="bg-blue-900/30 border border-blue-500/40 p-3.5 rounded-xl flex items-center justify-between text-xs">
        <span class="font-bold text-blue-300">
            <span x-text="selectedIds.length"></span> order(s) selected
        </span>
        <div class="flex items-center space-x-3">
            <form method="POST" action="{{ route('orders.bulk') }}" class="flex items-center space-x-2">
                @csrf
                <input type="hidden" name="action" value="status_update">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="order_ids[]" :value="id">
                </template>
                <select name="status" class="px-2.5 py-1 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white">
                    <option value="">Update Status...</option>
                    @foreach(['Pending', 'Purchased', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Refunded'] as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg">Apply Status</button>
            </form>

            @if(Auth::user()?->hasPermission('action_delete_order'))
            <form method="POST" action="{{ route('orders.bulk') }}" onsubmit="return confirm('Are you sure you want to delete selected orders?');">
                @csrf
                <input type="hidden" name="action" value="delete">
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="order_ids[]" :value="id">
                </template>
                <button type="submit" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-lg">Delete Selected</button>
            </form>
            @endif
        </div>
    </div>
    @endif

    <!-- Orders Data Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950/80 text-slate-400 border-b border-slate-800 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-3 px-4 w-10 text-center">
                            @if(Auth::user()?->hasPermission('action_bulk_orders'))
                            <input type="checkbox" @change="selectAll = !selectAll; if(selectAll) { selectedIds = [{{ $orders->pluck('id')->implode(',') }}] } else { selectedIds = [] }"
                                   class="rounded bg-slate-950 border-slate-800 text-blue-600">
                            @endif
                        </th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">eBay Order</th>
                        <th class="py-3 px-4 text-center">Tracking</th>
                        <th class="py-3 px-4 text-right">Cust. Price</th>
                        <th class="py-3 px-4 text-right">Ad Fee</th>
                        <th class="py-3 px-4">Amazon Order</th>
                        <th class="py-3 px-4 text-right">Amazon Price</th>
                        <th class="py-3 px-4 text-right">E_NET</th>
                        <th class="py-3 px-4 text-right">Profit</th>
                        <th class="py-3 px-4 text-right">ROI (%)</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 text-center">
                            @if(Auth::user()?->hasPermission('action_bulk_orders'))
                            <input type="checkbox" :value="{{ $order->id }}" x-model="selectedIds"
                                   class="rounded bg-slate-950 border-slate-800 text-blue-600">
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap">
                            {{ $order->order_date ? $order->order_date->format('Y-m-d') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-white whitespace-nowrap">
                            <a href="{{ route('orders.show', $order) }}" class="hover:text-blue-400 transition">
                                {{ $order->ebay_order_number }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if(!empty($order->ebay_tracking_number))
                                <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30" title="Tracking #: {{ $order->ebay_tracking_number }}">
                                    <i class="fa-solid fa-check text-[9px]"></i>
                                    <span>Added</span>
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">
                            {{ $currencySymbol }}{{ number_format($order->customer_price, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-rose-400 font-semibold whitespace-nowrap">
                            {{ $currencySymbol }}{{ number_format($order->ad_fee_charges, 2) }}
                        </td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">
                            {{ $order->amazon_order_number ?: '-' }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">
                            {{ $currencySymbol }}{{ number_format($order->supplier_cost, 2) }}
                        </td>
                        <td class="py-3 px-4 text-right text-slate-200 font-bold whitespace-nowrap">
                            {{ $currencySymbol }}{{ number_format($order->ebay_net, 2) }}
                        </td>

                        <!-- Profit Column -->
                        <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap {{ $order->profit > 0 ? 'text-emerald-400' : ($order->profit < 0 ? 'text-rose-400' : 'text-slate-400') }}">
                            {{ $currencySymbol }}{{ number_format($order->profit, 2) }}
                        </td>

                        <!-- ROI Badge Column -->
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            @php
                                $roiPct = $order->roi * 100;
                                $badgeClass = $roiPct >= 20 
                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' 
                                    : ($roiPct >= 10 
                                        ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' 
                                        : 'bg-rose-500/10 text-rose-400 border-rose-500/30');
                            @endphp
                            <span class="inline-block px-2 py-0.5 font-bold rounded-lg border text-[11px] {{ $badgeClass }}">
                                {{ number_format($roiPct, 2) }}%
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @php
                                $statusClasses = [
                                    'Pending' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
                                    'Purchased' => 'bg-blue-500/15 text-blue-300 border-blue-500/30',
                                    'Shipped' => 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30',
                                    'Delivered' => 'bg-teal-500/15 text-teal-300 border-teal-500/30',
                                    'Completed' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                                    'Cancelled' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
                                    'Refunded' => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
                                ];
                                $statusStyle = $statusClasses[$order->status] ?? 'bg-slate-800 text-slate-300 border-slate-700';
                            @endphp
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider border {{ $statusStyle }}">
                                {{ $order->status }}
                            </span>
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-3 px-4 text-right whitespace-nowrap space-x-1.5">
                            <a href="{{ route('orders.show', $order) }}" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            @if(Auth::user()?->hasPermission('action_edit_order'))
                            <a href="{{ route('orders.edit', $order) }}" class="p-1.5 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition" title="Edit Order">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            @endif

                            @if(Auth::user()?->hasPermission('action_delete_order'))
                            <form method="POST" action="{{ route('orders.destroy', $order) }}" class="inline" onsubmit="return confirm('Are you sure you want to soft delete order {{ $order->ebay_order_number }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" title="Delete Order">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="py-8 text-center text-slate-500">
                            <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                            <span>No orders match your filter criteria.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $orders->links() }}
        </div>
    </div>

</div>
@endsection
