@extends('layouts.app')

@section('title', 'Add New User')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', 'Operator') }}' }">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Users List</span>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-1">Create System User Account</h1>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            @if(Auth::user()->isSuperAdmin())
            <div>
                <label for="company_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Assign Company</label>
                <select name="company_id" id="company_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    <option value="">Global / Super Admin (No Company Scope)</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }} ({{ $company->code }})
                        </option>
                    @endforeach
                </select>
                <span class="text-[10px] text-slate-400 mt-1 block">Leave blank for global Super Admin users.</span>
            </div>
            @else
                <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
            @endif

            <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider border-b border-slate-800 pb-2">1. Account Details</h3>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Full Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Operator Name" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Address *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="operator@company.com" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">System Role *</label>
                    <select name="role" id="role" x-model="selectedRole" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                        @if(Auth::user()->isSuperAdmin())
                            <option value="SuperAdmin">Super Admin</option>
                            <option value="CompanyAdmin">Company Admin</option>
                        @endif
                        <option value="Operator">Operator</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Account Status *</label>
                    <select name="status" id="status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Password *</label>
                    <input type="password" name="password" id="password" required minlength="8"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    @error('password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirm Password *</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                </div>
            </div>

            <!-- Operator Access Control & Permissions Matrix -->
            <div x-show="selectedRole === 'Operator'" x-transition class="space-y-4 pt-4 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-teal-400 uppercase tracking-wider">2. Operator Access Control & Permissions</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Configure which navigation menu items and action buttons this operator can access.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Left Navigation Permissions -->
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                        <span class="text-xs font-extrabold text-blue-400 uppercase tracking-wider flex items-center space-x-2">
                            <i class="fa-solid fa-list-check"></i>
                            <span>Left Navigation Menu Items</span>
                        </span>

                        <div class="space-y-2.5 pt-1">
                            @php
                                $navPerms = array_filter(\App\Models\User::allPermissions(), fn($p) => str_starts_with($p['name'], 'Dashboard') || str_contains($p['category'], 'Navigation'));
                            @endphp
                            @foreach($navPerms as $key => $perm)
                            <label class="flex items-start space-x-3 p-2 rounded-lg hover:bg-slate-900 cursor-pointer transition">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" checked
                                       class="mt-0.5 rounded border-slate-700 bg-slate-900 text-blue-500 focus:ring-blue-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-200 block">{{ $perm['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $perm['description'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons Permissions -->
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 space-y-3">
                        <span class="text-xs font-extrabold text-amber-400 uppercase tracking-wider flex items-center space-x-2">
                            <i class="fa-solid fa-[#f59e0b] fa-bolt"></i>
                            <span>Action Buttons & Operations</span>
                        </span>

                        <div class="space-y-2.5 pt-1">
                            @php
                                $actionPerms = array_filter(\App\Models\User::allPermissions(), fn($p) => str_contains($p['category'], 'Action'));
                            @endphp
                            @foreach($actionPerms as $key => $perm)
                            <label class="flex items-start space-x-3 p-2 rounded-lg hover:bg-slate-900 cursor-pointer transition">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" checked
                                       class="mt-0.5 rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-amber-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-200 block">{{ $perm['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $perm['description'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-800">
                <a href="{{ route('users.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/25 transition">
                    Create User & Send Credentials Email
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
