@extends('layouts.app')

@section('title', 'Import Data Preview')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Pre-Import Spreadsheet Validation</h1>
            <p class="text-xs text-slate-400 mt-1">Review detected records, column mappings, and validation checks before saving.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('import.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl transition">
                Cancel
            </a>
            <form method="POST" action="{{ route('import.confirm') }}" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-emerald-600/25 transition flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-circle-check'"></i>
                    <span x-text="isSubmitting ? 'Importing Data into Orders...' : 'Confirm Import ({{ $preview['total_rows'] - $preview['invalid_count'] }} Rows)'"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Matrix Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Detected</span>
            <p class="text-xl font-extrabold text-white mt-1">{{ $preview['total_rows'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">New Records</span>
            <p class="text-xl font-extrabold text-emerald-400 mt-1">{{ $preview['new_count'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Existing / Duplicates</span>
            <p class="text-xl font-extrabold text-indigo-400 mt-1">{{ $preview['existing_count'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Invalid Rows</span>
            <p class="text-xl font-extrabold text-rose-400 mt-1">{{ $preview['invalid_count'] }}</p>
        </div>
        <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl shadow-lg">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Import Mode</span>
            <p class="text-sm font-extrabold text-blue-400 mt-1 uppercase">{{ $mode }}</p>
        </div>
    </div>

    <!-- Row Preview Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 border-b border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <h3 class="font-bold text-xs text-white uppercase tracking-wider">Spreadsheet Row Preview Matrix</h3>
            <span class="text-xs text-slate-400">Headers detected at Row {{ $preview['header_row'] }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">Row</th>
                        <th class="py-3 px-4">Order Date</th>
                        <th class="py-3 px-4">eBay Order</th>
                        <th class="py-3 px-4 text-right">Cust. Price</th>
                        <th class="py-3 px-4 text-right">Ad Fee</th>
                        <th class="py-3 px-4">Amazon Order</th>
                        <th class="py-3 px-4 text-right">Supplier Cost</th>
                        <th class="py-3 px-4 text-right">E_NET</th>
                        <th class="py-3 px-4 text-right">Profit</th>
                        <th class="py-3 px-4 text-right">ROI (%)</th>
                        <th class="py-3 px-4 text-center">Validation Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($preview['rows'] as $row)
                    <tr class="hover:bg-slate-800/40 transition {{ $row['status'] === 'invalid' ? 'bg-rose-950/20' : '' }}">
                        <td class="py-3 px-4 text-center text-slate-500 font-mono">#{{ $row['row_index'] }}</td>
                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap">{{ $row['order_date'] ?: $row['raw_date'] }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-white whitespace-nowrap">{{ $row['ebay_order_number'] ?: $row['raw_ebay'] }}</td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">€{{ number_format($row['customer_price'], 2) }}</td>
                        <td class="py-3 px-4 text-right text-rose-400 font-semibold whitespace-nowrap">€{{ number_format($row['ad_fee_charges'], 2) }}</td>
                        <td class="py-3 px-4 font-mono text-slate-400 whitespace-nowrap">{{ $row['amazon_order_number'] ?: $row['raw_amazon'] }}</td>
                        <td class="py-3 px-4 text-right text-slate-300 font-semibold whitespace-nowrap">€{{ number_format($row['supplier_cost'], 2) }}</td>
                        <td class="py-3 px-4 text-right text-slate-200 font-bold whitespace-nowrap">€{{ number_format($row['ebay_net'], 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold whitespace-nowrap {{ $row['profit'] > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                            €{{ number_format($row['profit'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-right font-bold text-blue-400 whitespace-nowrap">
                            {{ number_format($row['roi'] * 100, 2) }}%
                        </td>

                        <!-- Validation Status -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($row['status'] === 'new')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">NEW</span>
                            @elseif($row['status'] === 'existing')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">EXISTING</span>
                            @elseif($row['status'] === 'duplicate_in_file')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">DUPLICATE</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20" title="{{ implode(', ', $row['errors']) }}">INVALID</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
