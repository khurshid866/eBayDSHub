@extends('layouts.app')

@section('title', 'Company Management')

@section('content')
<div class="space-y-6" x-data="{ resetCompanyId: null, resetCompanyName: '' }">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Registered Multi-Tenant Companies</h1>
            <p class="text-xs text-slate-400 mt-1">Super Admin portal to create, configure, reset passwords, and manage enterprise client organizations.</p>
        </div>
        <a href="{{ route('companies.create') }}" class="px-4 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-600/20 transition flex items-center space-x-2">
            <i class="fa-solid fa-building-circle-check"></i>
            <span>Register New Company</span>
        </a>
    </div>

    <!-- Companies Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Company Name</th>
                        <th class="py-3 px-4">Code / Slug</th>
                        <th class="py-3 px-4">Company Admin</th>
                        <th class="py-3 px-4 text-center">Assigned Users</th>
                        <th class="py-3 px-4 text-center">Total Orders</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($companies as $company)
                    @php $admin = $company->users->first(); @endphp
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center border border-amber-500/30">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <span>{{ $company->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">{{ $company->code }}</td>
                        <td class="py-3 px-4 text-slate-300">
                            @if($admin)
                                <span class="font-bold text-white block">{{ $admin->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $admin->email }}</span>
                            @else
                                <span class="text-slate-500 italic">No Admin assigned</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-teal-400">{{ number_format($company->users_count) }}</td>
                        <td class="py-3 px-4 text-center font-bold text-blue-400">{{ number_format($company->orders_count) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $company->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                                {{ $company->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                            @if($admin)
                            <!-- Resend Credentials Email -->
                            <form method="POST" action="{{ route('companies.resend-credentials', $company) }}" class="inline" onsubmit="return confirm('Resend access credentials email to Company Admin ({{ $admin->email }})?');">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-blue-400 hover:bg-slate-800 rounded-lg transition" title="Resend Credentials Email to {{ $admin->email }}">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </button>
                            </form>

                            <!-- Quick Reset Company Admin Password Button -->
                            <button type="button" @click="resetCompanyId = {{ $company->id }}; resetCompanyName = '{{ addslashes($company->name) }}'"
                                    class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition" title="Reset Admin Password for {{ $company->name }}">
                                <i class="fa-solid fa-key"></i>
                            </button>
                            @endif

                            <a href="{{ route('companies.edit', $company) }}" class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition" title="Edit Company">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $companies->links() }}
        </div>
    </div>

    <!-- Quick Reset Company Admin Password Modal -->
    <div x-show="resetCompanyId !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-2xl w-full max-w-md space-y-4" @click.outside="resetCompanyId = null">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-white text-base">Reset Admin Password for <span x-text="resetCompanyName" class="text-amber-400"></span></h3>
                <button type="button" @click="resetCompanyId = null" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" :action="'/companies/' + resetCompanyId + '/reset-admin-password'" class="space-y-4">
                @csrf

                <div>
                    <label for="modal_company_password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">New Admin Password *</label>
                    <input type="password" name="password" id="modal_company_password" required minlength="8"
                           placeholder="At least 8 characters"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div>
                    <label for="modal_company_password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Confirm New Password *</label>
                    <input type="password" name="password_confirmation" id="modal_company_password_confirmation" required minlength="8"
                           placeholder="Re-enter password"
                           class="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 focus:border-amber-500 rounded-xl text-sm text-white">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="resetCompanyId = null" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-600/25">Reset Admin Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
