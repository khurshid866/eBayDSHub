@extends('layouts.app')

@section('title', 'Register New Company')

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    name: '{{ old('name') }}',
    code: '{{ old('code') }}',

    slugify() {
        if (!this.name) {
            this.code = '';
            return;
        }
        this.code = this.name.toLowerCase()
            .trim()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }
}">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('companies.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Companies List</span>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-1">Register Enterprise Client Company</h1>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('companies.store') }}" class="space-y-5">
            @csrf

            <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider border-b border-slate-800 pb-2">1. Company Details</h3>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Name *</label>
                <input type="text" name="name" id="name" x-model="name" @input="slugify()" placeholder="e.g. Apex Global Trading" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="code" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Unique Code / Slug *</label>
                <input type="text" name="code" id="code" x-model="code" placeholder="apex-global-trading (auto-generated from name)" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm font-mono text-white">
                <span class="text-[10px] text-slate-400 mt-1 block">Auto-generated from Company Name in real-time.</span>
                @error('code') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Status *</label>
                <select name="status" id="status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider border-b border-slate-800 pb-2 pt-4">2. Default Company Admin Credentials</h3>
            <p class="text-[11px] text-slate-400">An Admin user account will be created automatically and welcome credentials sent to their email.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="admin_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Admin Name *</label>
                    <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" placeholder="John Doe" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                    @error('admin_name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="admin_email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Admin Email *</label>
                    <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" placeholder="admin@company.com" required
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                    @error('admin_email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('companies.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-600/25 transition">
                    Register Company & Send Credentials Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
