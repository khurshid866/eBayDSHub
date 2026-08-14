@extends('layouts.app')

@section('title', 'System Audit Trail Logs')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">System Audit Trail Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Full immutable audit trail tracking user actions, logins, order changes, and configuration edits.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-slate-900 border border-slate-800 p-4 rounded-2xl shadow-xl">
        <form method="GET" action="{{ route('audit_logs.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-64">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="Filter by action name (e.g. order)..."
                       class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-semibold transition">
                Filter Logs
            </button>
            <a href="{{ route('audit_logs.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded-xl text-xs font-semibold transition">
                Reset
            </a>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Target Model</th>
                        <th class="py-3 px-4">IP Address</th>
                        <th class="py-3 px-4">Changes Preview</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 text-slate-400 whitespace-nowrap font-mono text-[11px]">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="py-3 px-4 font-bold text-white whitespace-nowrap">
                            {{ $log->user->name ?? 'System' }}
                        </td>
                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-purple-500/10 text-purple-400 border border-purple-500/30">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-300 font-mono text-[11px]">
                            {{ $log->model_type ? class_basename($log->model_type) . ' #' . $log->model_id : '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-400 font-mono text-[11px]">
                            {{ $log->ip_address ?: 'N/A' }}
                        </td>
                        <td class="py-3 px-4 text-slate-400 font-mono text-[10px] max-w-xs truncate">
                            @if(!empty($log->new_values))
                                {{ json_encode($log->new_values) }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No audit logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
