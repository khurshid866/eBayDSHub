@extends('layouts.app')

@section('title', 'Import History Log')

@section('content')
<div class="space-y-6">

    <!-- Header Banner -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Import Execution History Log</h1>
            <p class="text-xs text-slate-400 mt-1">Audit log of all past Excel upload operations and error summaries.</p>
        </div>
        <a href="{{ route('import.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>New Import</span>
        </a>
    </div>

    <!-- History Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Batch ID</th>
                        <th class="py-3 px-4">Filename</th>
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4 text-center">Total Rows</th>
                        <th class="py-3 px-4 text-center">Inserted</th>
                        <th class="py-3 px-4 text-center">Updated</th>
                        <th class="py-3 px-4 text-center">Skipped</th>
                        <th class="py-3 px-4 text-center">Failed</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Date & Time</th>
                        <th class="py-3 px-4 text-right">Error Log</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 font-mono font-bold text-blue-400">#{{ $batch->id }}</td>
                        <td class="py-3 px-4 font-bold text-white">{{ $batch->original_filename }}</td>
                        <td class="py-3 px-4 text-slate-400">{{ $batch->user->name ?? 'System' }}</td>
                        <td class="py-3 px-4 text-center text-slate-300 font-semibold">{{ number_format($batch->total_rows) }}</td>
                        <td class="py-3 px-4 text-center font-bold text-emerald-400">{{ number_format($batch->inserted_rows) }}</td>
                        <td class="py-3 px-4 text-center font-bold text-indigo-400">{{ number_format($batch->updated_rows) }}</td>
                        <td class="py-3 px-4 text-center text-slate-400">{{ number_format($batch->skipped_rows) }}</td>
                        <td class="py-3 px-4 text-center font-bold {{ $batch->failed_rows > 0 ? 'text-rose-400' : 'text-slate-500' }}">{{ number_format($batch->failed_rows) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $batch->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right text-slate-400 whitespace-nowrap">{{ $batch->created_at->format('M d, Y H:i') }}</td>
                        <td class="py-3 px-4 text-right">
                            @if(!empty($batch->error_summary))
                                <a href="{{ route('import.errors', $batch) }}" class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-lg text-[10px] font-bold transition inline-flex items-center space-x-1">
                                    <i class="fa-solid fa-download"></i>
                                    <span>Error Log</span>
                                </a>
                            @else
                                <span class="text-slate-600 text-[10px]">Clean</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-8 text-center text-slate-500">
                            <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
                            <span>No import history logs found.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800/80 bg-slate-950/40">
            {{ $batches->links() }}
        </div>
    </div>

</div>
@endsection
