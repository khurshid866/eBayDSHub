@extends('layouts.app')

@section('title', 'Company Settings & Branding')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Company Settings & Branding</h1>
            <p class="text-xs text-slate-400 mt-1">Configure company preferences, defaults, thresholds, and upload your custom logo.</p>
        </div>
    </div>

    <!-- Main Settings Form -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-6">
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Section 1: Company Logo & Branding -->
            <div class="border-b border-slate-800 pb-6 space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
                    <i class="fa-solid fa-image text-rose-400"></i>
                    <span>Company Branding & Logo</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 items-center">
                    <!-- Current Logo Preview -->
                    <div class="p-4 bg-slate-950 border border-slate-800 rounded-xl flex flex-col items-center justify-center text-center space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Current Logo</span>
                        @if($company && $company->logo)
                            <img src="{{ asset($company->logo) }}" alt="Company Logo" class="h-16 w-auto max-w-[180px] object-contain rounded-lg">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-600">
                                <i class="fa-solid fa-building text-2xl"></i>
                            </div>
                            <span class="text-[10px] text-slate-500">No custom logo uploaded</span>
                        @endif
                    </div>

                    <!-- Upload Form Control -->
                    <div class="sm:col-span-2 space-y-3">
                        @if($company)
                        <div>
                            <label for="company_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Company Display Name</label>
                            <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $company->name) }}"
                                   class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-rose-500 rounded-xl text-sm text-white">
                        </div>
                        @endif

                        <div>
                            <label for="company_logo" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1">Upload New Logo Image</label>
                            <input type="file" name="company_logo" id="company_logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                   class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700 cursor-pointer">
                            <span class="text-[10px] text-slate-500 mt-1 block">Supported formats: PNG, JPG, WEBP, SVG (Max 2MB).</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Financial & Display Preferences -->
            <div class="space-y-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider border-b border-slate-800 pb-3 flex items-center space-x-2">
                    <i class="fa-solid fa-sliders text-blue-400"></i>
                    <span>Financial & Display Preferences</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Application Title -->
                    <div>
                        <label for="app_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">System Title</label>
                        <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $settings['app_name']) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    </div>

                    <!-- Currency Symbol Dropdown Menu -->
                    <div>
                        <label for="currency_symbol" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Currency Symbol *</label>
                        <select name="currency_symbol" id="currency_symbol" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            @php
                                $currencies = [
                                    '€' => '€ — Euro (EUR)',
                                    '$' => '$ — US Dollar (USD)',
                                    '£' => '£ — British Pound (GBP)',
                                    'C$' => 'C$ — Canadian Dollar (CAD)',
                                    'A$' => 'A$ — Australian Dollar (AUD)',
                                    '¥' => '¥ — Japanese Yen / Chinese Yuan (JPY / CNY)',
                                    '₹' => '₹ — Indian Rupee (INR)',
                                    'CHF' => 'CHF — Swiss Franc (CHF)',
                                    'kr' => 'kr — Swedish / Norwegian / Danish Krone (SEK / NOK / DKK)',
                                    'R$' => 'R$ — Brazilian Real (BRL)',
                                    '₱' => '₱ — Philippine Peso (PHP)',
                                    'zł' => 'zł — Polish Zloty (PLN)',
                                    'Rs' => 'Rs — Pakistani Rupee (PKR)',
                                    'AED' => 'AED — United Arab Emirates Dirham (AED)',
                                    'SAR' => 'SAR — Saudi Riyal (SAR)',
                                    'KSh' => 'KSh — Kenyan Shilling (KES)',
                                    '₺' => '₺ — Turkish Lira (TRY)',
                                    '₿' => '₿ — Bitcoin / Crypto (BTC)',
                                ];
                                $currentCurr = old('currency_symbol', $settings['currency_symbol'] ?? '€');
                            @endphp

                            @foreach($currencies as $symbol => $label)
                                <option value="{{ $symbol }}" {{ $currentCurr === $symbol ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach

                            @if(!array_key_exists($currentCurr, $currencies))
                                <option value="{{ $currentCurr }}" selected>{{ $currentCurr }} (Custom Symbol)</option>
                            @endif
                        </select>
                    </div>

                    <!-- Default Order Status -->
                    <div>
                        <label for="default_order_status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Default Imported Order Status</label>
                        <select name="default_order_status" id="default_order_status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            @foreach(['Pending', 'Purchased', 'Shipped', 'Delivered', 'Completed', 'Cancelled', 'Refunded'] as $st)
                                <option value="{{ $st }}" {{ old('default_order_status', $settings['default_order_status']) === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Default Import Mode -->
                    <div>
                        <label for="default_import_mode" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Default Excel Import Mode</label>
                        <select name="default_import_mode" id="default_import_mode" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            <option value="create" {{ old('default_import_mode', $settings['default_import_mode']) === 'create' ? 'selected' : '' }}>Create New Records Only</option>
                            <option value="update" {{ old('default_import_mode', $settings['default_import_mode']) === 'update' ? 'selected' : '' }}>Update Existing & Create New</option>
                        </select>
                    </div>

                    <!-- ROI Warning Threshold -->
                    <div>
                        <label for="roi_warning_threshold" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Low ROI Warning Threshold (%)</label>
                        <input type="number" step="0.1" name="roi_warning_threshold" id="roi_warning_threshold" value="{{ old('roi_warning_threshold', $settings['roi_warning_threshold']) }}" required
                               class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    </div>

                    <!-- Pagination Size -->
                    <div>
                        <label for="pagination_size" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Default Table Pagination Rows</label>
                        <select name="pagination_size" id="pagination_size" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            @foreach([10, 15, 25, 50, 100] as $sz)
                                <option value="{{ $sz }}" {{ old('pagination_size', $settings['pagination_size']) == $sz ? 'selected' : '' }}>{{ $sz }} rows per page</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <!-- Submit Action Button -->
            <div class="flex items-center justify-end pt-4 border-t border-slate-800">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/25 transition">
                    Save Company Settings & Branding
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
