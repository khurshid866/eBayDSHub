@extends('layouts.app')

@section('title', 'Add New Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    ebay_order_number: '{{ old('ebay_order_number') }}',
    amazon_order_number: '{{ old('amazon_order_number') }}',
    customer_price: {{ old('customer_price', 0) }},
    ad_fee_charges: {{ old('ad_fee_charges', 0) }},
    supplier_cost: {{ old('supplier_cost', 0) }},

    formatEbay() {
        let raw = (this.ebay_order_number || '').replace(/[^\d]/g, '').substring(0, 12);
        if (raw.length > 7) {
            this.ebay_order_number = raw.substring(0, 2) + '-' + raw.substring(2, 7) + '-' + raw.substring(7);
        } else if (raw.length > 2) {
            this.ebay_order_number = raw.substring(0, 2) + '-' + raw.substring(2);
        } else {
            this.ebay_order_number = raw;
        }
    },

    formatAmazon() {
        let raw = (this.amazon_order_number || '').replace(/[^\d]/g, '').substring(0, 17);
        if (raw.length > 10) {
            this.amazon_order_number = raw.substring(0, 3) + '-' + raw.substring(3, 10) + '-' + raw.substring(10);
        } else if (raw.length > 3) {
            this.amazon_order_number = raw.substring(0, 3) + '-' + raw.substring(3);
        } else {
            this.amazon_order_number = raw;
        }
    },

    get ebay_net() {
        let cust = parseFloat(this.customer_price) || 0;
        let adFee = parseFloat(this.ad_fee_charges) || 0;
        return (cust - adFee).toFixed(2);
    },

    get profit() {
        let net = parseFloat(this.ebay_net) || 0;
        let amazonPrice = parseFloat(this.supplier_cost) || 0;
        return (net - amazonPrice).toFixed(2);
    },

    get roi() {
        let net = parseFloat(this.ebay_net) || 0;
        let p = parseFloat(this.profit) || 0;
        if (net <= 0) return '0.00';
        return ((p / net) * 100).toFixed(2);
    }
}">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Orders List</span>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-1">Create New eBay Order</h1>
        </div>
    </div>

    <!-- Live Financial Formula Preview Box -->
    <div class="bg-gradient-to-r from-blue-950/60 to-indigo-950/60 border border-blue-500/30 p-5 rounded-2xl shadow-xl grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Customer Price (eBay)</span>
            <p class="text-lg font-extrabold text-white">{{ $currencySymbol }}<span x-text="parseFloat(customer_price || 0).toFixed(2)"></span></p>
        </div>
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ad Fee Charges</span>
            <p class="text-lg font-extrabold text-rose-400">{{ $currencySymbol }}<span x-text="parseFloat(ad_fee_charges || 0).toFixed(2)"></span></p>
        </div>
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">E_NET Value</span>
            <p class="text-lg font-extrabold text-slate-200">{{ $currencySymbol }}<span x-text="ebay_net"></span></p>
            <span class="text-[9px] text-slate-500 block">Cust. Price - Ad Fee</span>
        </div>
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Calculated Profit</span>
            <p class="text-lg font-extrabold" :class="profit > 0 ? 'text-emerald-400' : (profit < 0 ? 'text-rose-400' : 'text-slate-400')">
                {{ $currencySymbol }}<span x-text="profit"></span>
            </p>
            <span class="text-[9px] text-slate-500 block">E_NET - Amazon Price</span>
        </div>
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Calculated ROI</span>
            <p class="text-lg font-extrabold text-blue-400">
                <span x-text="roi"></span>%
            </p>
            <span class="text-[9px] text-slate-500 block">Profit / E_NET</span>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('orders.store') }}" class="space-y-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Order Date -->
                <div>
                    <label for="order_date" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Order Date *</label>
                    <div class="relative">
                        <input type="date" name="order_date" id="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-blue-400">
                            <i class="fa-solid fa-calendar-days text-sm"></i>
                        </span>
                    </div>
                    @error('order_date') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- eBay Order Number -->
                <div>
                    <label for="ebay_order_number" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">eBay Order Number *</label>
                    <input type="text" name="ebay_order_number" id="ebay_order_number" x-model="ebay_order_number" @input="formatEbay()" @blur="formatEbay()"
                           placeholder="27-14904-69608" required maxlength="14"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm font-mono text-white">
                    <span class="text-[10px] text-slate-400 mt-1 block">Pattern: XX-XXXXX-XXXXX (Auto-formats digits)</span>
                    @error('ebay_order_number') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- eBay Tracking Number -->
                <div>
                    <label for="ebay_tracking_number" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">eBay Tracking Number</label>
                    <input type="text" name="ebay_tracking_number" id="ebay_tracking_number" value="{{ old('ebay_tracking_number') }}"
                           placeholder="e.g. 9400111899562912345678"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm font-mono text-white">
                    <span class="text-[10px] text-slate-400 mt-1 block">Optional package tracking code</span>
                    @error('ebay_tracking_number') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Customer Price -->
                <div>
                    <label for="customer_price" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Customer Price (eBay) ({{ $currencySymbol }}) *</label>
                    <input type="number" step="0.01" min="0" name="customer_price" id="customer_price" x-model.number="customer_price" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    @error('customer_price') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Ad Fee Charges -->
                <div>
                    <label for="ad_fee_charges" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Ad Fee Charges ({{ $currencySymbol }})</label>
                    <input type="number" step="0.01" min="0" name="ad_fee_charges" id="ad_fee_charges" x-model.number="ad_fee_charges" placeholder="0.00"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    @error('ad_fee_charges') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- E_NET Value (Dynamically Calculated) -->
                <div>
                    <label for="ebay_net_display" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">E_NET Value ({{ $currencySymbol }})</label>
                    <input type="text" id="ebay_net_display" :value="'{{ $currencySymbol }}' + ebay_net" readonly tabindex="-1"
                           class="w-full px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-sm font-bold text-slate-200 cursor-not-allowed select-none">
                </div>

                <!-- Amazon Order Number -->
                <div>
                    <label for="amazon_order_number" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Amazon Order Number (Optional)</label>
                    <input type="text" name="amazon_order_number" id="amazon_order_number" x-model="amazon_order_number" @input="formatAmazon()" @blur="formatAmazon()"
                           placeholder="304-9841814-4365162 (Optional)" maxlength="19"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm font-mono text-white">
                    <span class="text-[10px] text-slate-400 mt-1 block">Pattern: XXX-XXXXXXX-XXXXXXX (Optional)</span>
                    @error('amazon_order_number') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Amazon Price (Supplier Cost) -->
                <div>
                    <label for="supplier_cost" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Amazon Price ({{ $currencySymbol }}) *</label>
                    <input type="number" step="0.01" min="0" name="supplier_cost" id="supplier_cost" x-model.number="supplier_cost" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    @error('supplier_cost') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Order Status *</label>
                    <select name="status" id="status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                        @foreach(['Pending', 'Purchased', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Refunded'] as $st)
                            <option value="{{ $st }}" {{ old('status', 'Completed') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Notes</label>
                <textarea name="notes" id="notes" rows="3" placeholder="Optional notes..."
                          class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">{{ old('notes') }}</textarea>
                @error('notes') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('orders.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/25 transition flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-check'"></i>
                    <span x-text="isSubmitting ? 'Saving Order...' : 'Save Order'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
