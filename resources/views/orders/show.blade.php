@extends('layouts.app')

@section('title', 'Order Details - ' . $order->ebay_order_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Actions & Navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Orders List</span>
            </a>
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
            <h1 class="text-2xl font-extrabold text-white mt-1 flex items-center space-x-3">
                <span class="font-mono text-blue-400">{{ $order->ebay_order_number }}</span>
                <span class="px-3 py-1 text-xs font-extrabold uppercase rounded-full border {{ $statusStyle }}">
                    {{ $order->status }}
                </span>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('orders.edit', $order) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center space-x-2">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Edit Order</span>
            </a>

            <form method="POST" action="{{ route('orders.destroy', $order) }}" onsubmit="return confirm('Are you sure you want to soft delete this order?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-rose-600/20 hover:bg-rose-600 text-rose-400 hover:text-white border border-rose-500/30 font-semibold text-xs rounded-xl transition flex items-center space-x-2">
                    <i class="fa-solid fa-trash"></i>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Financial Cards Row -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Customer Price</span>
            <p class="text-xl font-extrabold text-white mt-1">{{ $currencySymbol }}{{ number_format($order->customer_price, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Ad Fee Charges</span>
            <p class="text-xl font-extrabold text-rose-400 mt-1">{{ $currencySymbol }}{{ number_format($order->ad_fee_charges, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Amazon Price</span>
            <p class="text-xl font-extrabold text-indigo-400 mt-1">{{ $currencySymbol }}{{ number_format($order->supplier_cost, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">E_NET (Cut)</span>
            <p class="text-xl font-extrabold text-slate-200 mt-1">{{ $currencySymbol }}{{ number_format($order->ebay_net, 2) }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Net Profit</span>
            <p class="text-xl font-extrabold mt-1 {{ $order->profit > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                {{ $currencySymbol }}{{ number_format($order->profit, 2) }}
            </p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">ROI (%)</span>
            <p class="text-xl font-extrabold text-blue-400 mt-1">
                {{ number_format($order->roi * 100, 2) }}%
            </p>
        </div>
    </div>

    <!-- Order Metadata & Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- General Information -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-circle-info text-blue-400"></i>
                <span>Order Information</span>
            </h3>

            <div class="space-y-3 text-xs">
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Company:</span>
                    <span class="font-bold text-indigo-400">{{ $order->company->name ?? 'Default' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Order Date:</span>
                    <span class="font-bold text-white">{{ $order->order_date ? $order->order_date->format('F d, Y') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">eBay Order Number:</span>
                    <span class="font-mono font-bold text-blue-400">{{ $order->ebay_order_number }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Tracking Number:</span>
                    @if($order->ebay_tracking_number)
                        <span class="font-mono font-bold text-emerald-400 flex items-center space-x-1.5">
                            <i class="fa-solid fa-truck-fast text-[11px]"></i>
                            <span>{{ $order->ebay_tracking_number }}</span>
                        </span>
                    @else
                        <span class="text-slate-500 italic">Not Added</span>
                    @endif
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Amazon Order Number:</span>
                    <span class="font-mono font-bold text-slate-300">{{ $order->amazon_order_number ?: 'None' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-800/60">
                    <span class="text-slate-400">Created By:</span>
                    <span class="font-bold text-white">{{ $order->creator->name ?? 'System' }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="text-slate-400">Last Updated By:</span>
                    <span class="font-bold text-white">{{ $order->updater->name ?? 'None' }}</span>
                </div>
            </div>
        </div>

        <!-- Notes Card -->
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center space-x-2">
                <i class="fa-solid fa-note-sticky text-amber-400"></i>
                <span>Order Notes</span>
            </h3>

            <div class="text-xs text-slate-300 bg-slate-950 p-4 rounded-xl border border-slate-800 min-h-[120px]">
                {{ $order->notes ?: 'No notes attached to this order.' }}
            </div>
        </div>

    </div>

    <!-- Audit History Timeline -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center space-x-2">
            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i>
            <span>Audit Trail & History</span>
        </h3>

        <div class="space-y-4">
            @forelse($order->auditLogs as $log)
            <div class="flex items-start space-x-4 text-xs p-3 rounded-xl bg-slate-950/60 border border-slate-800/60">
                <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold shrink-0">
                    <i class="fa-solid fa-pen-nib"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-white uppercase tracking-wider text-[11px]">{{ str_replace('_', ' ', $log->action) }}</span>
                        <span class="text-[10px] text-slate-500">{{ $log->created_at->diffForHumans() }} ({{ $log->created_at->format('M d, Y H:i') }})</span>
                    </div>
                    <p class="text-slate-400 mt-1">
                        By <span class="text-slate-200 font-semibold">{{ $log->user->name ?? 'System' }}</span> (IP: {{ $log->ip_address ?? 'N/A' }})
                    </p>
                </div>
            </div>
            @empty
            <div class="p-4 text-center text-xs text-slate-500">
                No previous modifications recorded for this order.
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
