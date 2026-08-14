@extends('layouts.app')

@section('title', 'System User Management')

@section('content')
<div class="space-y-6" x-data="{ resetUserId: null, resetUserName: '', viewPassUserId: null, viewPassUserName: '', viewPassVal: '' }">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">
                {{ Auth::user()->isCompanyAdmin() ? 'Company Operators & Users' : 'System User Management' }}
            </h1>
            <p class="text-xs text-slate-400 mt-1">Manage user accounts, assign roles (Super Admin / Admin / Operator), view passwords, reset credentials, and access user portals.</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-600/20 transition flex items-center space-x-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Add User</span>
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4">Company</th>
                        <th class="py-3 px-4 text-center">ROLE</th>
                        @if(Auth::user()->isSuperAdmin())
                        <th class="py-3 px-4 text-center">Assigned Password</th>
                        @endif
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Last Login</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center border border-blue-500/30">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <span>{{ $user->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-300">{{ $user->email }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-300">
                            @if($user->company)
                                <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    <i class="fa-solid fa-building text-[10px]"></i>
                                    <span>{{ $user->company->name }}</span>
                                </span>
                            @else
                                <span class="text-amber-400 font-bold">Global (Super Admin)</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @php
                                $roleDisplay = match($user->role) {
                                    'SuperAdmin' => 'Super Admin',
                                    'CompanyAdmin' => 'Admin',
                                    default => 'Operator'
                                };
                                $roleClass = match($user->role) {
                                    'SuperAdmin' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                    'CompanyAdmin' => 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                                    default => 'bg-blue-500/10 text-blue-400 border-blue-500/30'
                                };
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $roleClass }}">
                                {{ $roleDisplay }}
                            </span>
                        </td>

                        <!-- Super Admin View Password Column -->
                        @if(Auth::user()->isSuperAdmin())
                        <td class="py-3 px-4 text-center font-mono" x-data="{ showPass: false }">
                            <div class="inline-flex items-center space-x-2 bg-slate-950 px-2.5 py-1 rounded-lg border border-slate-800">
                                <span x-text="showPass ? '{{ addslashes($user->getPlainPassword()) }}' : '••••••••'" class="text-slate-300 text-xs font-semibold"></span>
                                <button type="button" @click="showPass = !showPass" class="text-slate-400 hover:text-amber-400 transition" title="Toggle Password Visibility">
                                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </td>
                        @endif

                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $user->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                                {{ $user->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-400">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</td>
                        <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <!-- Resend Access Credentials Email Button -->
                            <form method="POST" action="{{ route('users.resend-credentials', $user) }}" class="inline" onsubmit="return confirm('Resend access credentials email to {{ $user->email }}?');">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition" title="Resend Access Credentials Email to {{ $user->email }}">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </form>

                            <!-- Super Admin Impersonate / Access Portal Button -->
                            @if(Auth::user()?->isSuperAdmin() && $user->id !== Auth::id())
                            <form method="POST" action="{{ route('impersonate.start', $user) }}" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-emerald-400 hover:text-emerald-300 hover:bg-slate-800 rounded-lg transition" title="Access Portal as {{ $user->name }}">
                                    <i class="fa-solid fa-user-gear"></i>
                                </button>
                            </form>
                            @endif

                            <!-- Quick Reset Password Button -->
                            <button type="button" @click="resetUserId = {{ $user->id }}; resetUserName = '{{ addslashes($user->name) }}'" 
                                    class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition" title="Reset Password">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            <!-- Edit User -->
                            <a href="{{ route('users.edit', $user) }}" class="p-1.5 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition" title="Edit User">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- Soft Delete User -->
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to soft-delete user {{ $user->name }}? Their entry history will remain safe.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" title="Soft Delete User">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Quick Reset Password Modal -->
    <div x-show="resetUserId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-2xl w-full max-w-md space-y-4" @click.outside="resetUserId = null">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">Reset Password for <span x-text="resetUserName" class="text-amber-400"></span></h3>
                <button type="button" @click="resetUserId = null" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" :action="'/users/' + resetUserId + '/reset-password'" class="space-y-4">
                @csrf

                <div>
                    <label for="modal_password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">New Password *</label>
                    <input type="password" name="password" id="modal_password" required minlength="8"
                           placeholder="At least 8 characters"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div>
                    <label for="modal_password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="modal_password_confirmation" required minlength="8"
                           placeholder="Re-enter password"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="resetUserId = null" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-600/25">Reset Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
