@extends('layouts.app')

@section('title', 'Edit User Account')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: '{{ old('role', $user->role) }}', isSubmitting: false }">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Users List</span>
            </a>
            <h1 class="text-xl font-extrabold text-white mt-1">Edit User: {{ $user->name }}</h1>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6" @submit="isSubmitting = true">
            @csrf
            @method('PUT')

            @if(Auth::user()->isSuperAdmin())
            <div>
                <label for="company_id" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Assign Company</label>
                <select name="company_id" id="company_id" class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    <option value="">Global / Super Admin (No Company Scope)</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }} ({{ $company->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @else
                <input type="hidden" name="company_id" value="{{ Auth::user()->company_id }}">
            @endif

            <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider border-b border-slate-800 pb-2">1. Account Information</h3>

            <div>
                <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Full Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                @error('name') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Address *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                @error('email') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="role" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">System Role *</label>
                    @if(Auth::user()->isSuperAdmin())
                        <select name="role" id="role" x-model="selectedRole" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            <option value="SuperAdmin" {{ old('role', $user->role) === 'SuperAdmin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="CompanyAdmin" {{ old('role', $user->role) === 'CompanyAdmin' ? 'selected' : '' }}>Company Admin</option>
                            <option value="Operator" {{ old('role', $user->role) === 'Operator' ? 'selected' : '' }}>Operator</option>
                        </select>
                    @elseif($user->isCompanyAdmin())
                        <div class="px-4 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-sm font-bold text-emerald-400 flex items-center space-x-2">
                            <i class="fa-solid fa-user-shield"></i>
                            <span>Company Admin (Fixed Role)</span>
                        </div>
                        <input type="hidden" name="role" value="CompanyAdmin">
                    @else
                        <select name="role" id="role" x-model="selectedRole" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                            <option value="CompanyAdmin" {{ old('role', $user->role) === 'CompanyAdmin' ? 'selected' : '' }}>Company Admin</option>
                            <option value="Operator" {{ old('role', $user->role) === 'Operator' ? 'selected' : '' }}>Operator</option>
                        </select>
                    @endif
                </div>

                <div>
                    <label for="status" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Account Status *</label>
                    <select name="status" id="status" required class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">New Password (Optional)</label>
                    <input type="password" name="password" id="password" minlength="8" placeholder="Leave blank to keep current"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-blue-500 rounded-xl text-sm text-white">
                    @error('password') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" minlength="8" placeholder="Leave blank to keep current"
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
                                $userPermissions = $user->permissions ?? array_keys(\App\Models\User::allPermissions());
                            @endphp
                            @foreach($navPerms as $key => $perm)
                            <label class="flex items-start space-x-3 p-2 rounded-lg hover:bg-slate-900 cursor-pointer transition">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ in_array($key, $userPermissions) ? 'checked' : '' }}
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
                            <i class="fa-solid fa-bolt"></i>
                            <span>Action Buttons & Operations</span>
                        </span>

                        <div class="space-y-2.5 pt-1">
                            @php
                                $actionPerms = array_filter(\App\Models\User::allPermissions(), fn($p) => str_contains($p['category'], 'Action'));
                            @endphp
                            @foreach($actionPerms as $key => $perm)
                            <label class="flex items-start space-x-3 p-2 rounded-lg hover:bg-slate-900 cursor-pointer transition">
                                <input type="checkbox" name="permissions[]" value="{{ $key }}" {{ in_array($key, $userPermissions) ? 'checked' : '' }}
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
                <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/25 transition flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-user-check'"></i>
                    <span x-text="isSubmitting ? 'Updating User Account...' : 'Update User Account & Access Permissions'"></span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
