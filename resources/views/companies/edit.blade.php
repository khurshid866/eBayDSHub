@extends('layouts.app')

@section('title', 'Edit Company - ' . $company->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('companies.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Companies List</span>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-1">Edit Company: {{ $company->name }}</h1>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('companies.update', $company) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="code" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Unique Code / Slug *</label>
                <input type="text" name="code" id="code" value="{{ old('code', $company->code) }}" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm font-mono text-white">
                @error('code') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Contact Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $company->email) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Contact Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $company->phone) }}"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>
            </div>

            <div>
                <label for="status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Company Status *</label>
                <select name="status" id="status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                    <option value="active" {{ old('status', $company->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $company->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('companies.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-amber-600/25 transition">
                    Update Company
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
