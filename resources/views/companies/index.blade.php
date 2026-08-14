@extends('layouts.app')

@section('title', 'Company Management')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', $activeTab) }}', resetCompanyId: null, resetCompanyName: '' }">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Registered Multi-Tenant Companies</h1>
            <p class="text-xs text-slate-400 mt-1">Super Admin portal to manage client organizations, toggle active statuses, soft delete, and restore archived companies.</p>
        </div>
        <a href="{{ route('companies.create') }}" class="px-4 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-amber-600/20 transition flex items-center space-x-2">
            <i class="fa-solid fa-building-circle-check"></i>
            <span>Register New Company</span>
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center space-x-2 border-b border-slate-800 pb-3">
        <a href="{{ route('companies.index', ['tab' => 'active']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center space-x-2 {{ request('tab', 'active') === 'active' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30 shadow-md shadow-amber-500/5' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <i class="fa-solid fa-building"></i>
            <span>Active Companies</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ request('tab', 'active') === 'active' ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                {{ number_format($companies->total()) }}
            </span>
        </a>

        <a href="{{ route('companies.index', ['tab' => 'archived']) }}"
           class="px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center space-x-2 {{ request('tab') === 'archived' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/30 shadow-md shadow-rose-500/5' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
            <i class="fa-solid fa-box-archive"></i>
            <span>Archived / Deleted Companies</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ request('tab') === 'archived' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-800 text-slate-400' }}">
                {{ number_format($deletedCompanies->total()) }}
            </span>
        </a>
    </div>

    @if(request('tab') === 'archived')
    <!-- Archived / Deleted Companies Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-4 bg-slate-950/60 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-300 flex items-center space-x-2">
                <i class="fa-solid fa-trash-can text-rose-400"></i>
                <span>Soft Deleted Companies Archive</span>
            </h3>
            <span class="text-xs text-slate-500">Archived companies are hidden from multi-tenant active contexts but can be restored anytime.</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Company Name</th>
                        <th class="py-3 px-4">Last Admin</th>
                        <th class="py-3 px-4 text-center">Assigned Users</th>
                        <th class="py-3 px-4 text-center">Orders History</th>
                        <th class="py-3 px-4 text-right">Archived At</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($deletedCompanies as $company)
                    @php $admin = $company->users->first(); @endphp
                    <tr class="hover:bg-slate-800/40 transition opacity-80 hover:opacity-100">
                        <td class="py-3 px-4 font-bold text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-400 font-bold flex items-center justify-center border border-rose-500/30">
                                <i class="fa-solid fa-building-slash"></i>
                            </div>
                            <div>
                                <span class="line-through text-slate-300 block">{{ $company->name }}</span>
                                <span class="text-[10px] text-slate-500 font-normal">ID #{{ $company->id }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-slate-300">
                            @if($admin)
                                <span class="font-bold text-slate-300 block">{{ $admin->name }}</span>
                                <span class="text-[10px] text-slate-500">{{ $admin->email }}</span>
                            @else
                                <span class="text-slate-500 italic">None</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-slate-400">{{ number_format($company->users_count) }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-400">{{ number_format($company->orders_count) }}</td>
                        <td class="py-3 px-4 text-right text-rose-300 font-mono text-[11px]">
                            {{ $company->deleted_at ? $company->deleted_at->format('M d, Y H:i') : 'Unknown' }}
                        </td>
                        <td class="py-3 px-4 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('companies.restore', $company->id) }}" class="inline" onsubmit="return confirm('Restore company {{ addslashes($company->name) }} back to active list?');">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 rounded-xl text-xs font-bold transition inline-flex items-center space-x-1.5" title="Restore Company">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Restore Company</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 italic">No archived / deleted companies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $deletedCompanies->links() }}
        </div>
    </div>

    @else

    <!-- Active Companies Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Company Name</th>
                        <th class="py-3 px-4">Company Admin</th>
                        <th class="py-3 px-4 text-center">Assigned Users</th>
                        <th class="py-3 px-4 text-center">Total Orders</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($companies as $company)
                    @php $admin = $company->users->first(); @endphp
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-bold text-white flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center border border-amber-500/30">
                                <i class="fa-solid fa-building"></i>
                            </div>
                            <span>{{ $company->name }}</span>
                        </td>
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
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase {{ $company->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30' }}">
                                {{ $company->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <!-- Direct Quick Toggle Status Action Button -->
                            <form method="POST" action="{{ route('companies.toggle-status', $company) }}" class="inline" onsubmit="return confirm('Toggle status of {{ addslashes($company->name) }} to {{ $company->status === 'active' ? 'INACTIVE' : 'ACTIVE' }}?');">
                                @csrf
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 rounded-lg transition" title="Directly Change Status (Currently {{ strtoupper($company->status) }})">
                                    @if($company->status === 'active')
                                        <i class="fa-solid fa-toggle-on text-emerald-400 text-sm"></i>
                                    @else
                                        <i class="fa-solid fa-toggle-off text-slate-500 text-sm"></i>
                                    @endif
                                </button>
                            </form>

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

                            <!-- Edit Company -->
                            <a href="{{ route('companies.edit', $company) }}" class="p-1.5 text-slate-400 hover:text-amber-400 hover:bg-slate-800 rounded-lg transition" title="Edit Company Settings">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <!-- Soft Delete / Archive Company -->
                            <form method="POST" action="{{ route('companies.destroy', $company) }}" class="inline" onsubmit="return confirm('Archive company {{ addslashes($company->name) }}? Company will move to Archived tab.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition" title="Soft Delete / Archive Company">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500 italic">No registered active companies found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $companies->links() }}
        </div>
    </div>
    @endif

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
