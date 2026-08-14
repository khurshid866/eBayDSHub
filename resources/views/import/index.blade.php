@extends('layouts.app')

@section('title', 'Excel Import Module')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Import Orders Excel Spreadsheet</h1>
            <p class="text-xs text-slate-400 mt-1">Upload .xlsx, .xls, or .csv files to bulk insert or update eBay orders.</p>
        </div>
        
        <div class="flex items-center space-x-3">
            <a href="{{ route('import.template') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-emerald-400 hover:text-white border border-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-2">
                <i class="fa-solid fa-download"></i>
                <span>Download Sample Template</span>
            </a>
            <a href="{{ route('import.history') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-2">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Import History</span>
            </a>
        </div>
    </div>

    <!-- Upload Card -->
    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-xl">
        <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
            @csrf

            <!-- Mode Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Import Processing Mode *</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="p-4 rounded-xl bg-slate-950 border border-slate-800 hover:border-blue-500/50 cursor-pointer flex items-start space-x-3 transition">
                        <input type="radio" name="mode" value="create" checked class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-bold text-sm text-white block">Create New Records Only</span>
                            <span class="text-xs text-slate-400">Inserts new orders. Existing orders with duplicate eBay order numbers are skipped.</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-xl bg-slate-950 border border-slate-800 hover:border-blue-500/50 cursor-pointer flex items-start space-x-3 transition">
                        <input type="radio" name="mode" value="update" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="font-bold text-sm text-white block">Update Existing & Create New</span>
                            <span class="text-xs text-slate-400">Updates existing matching orders by eBay order number, and creates new ones if missing.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- File Upload Drop Zone -->
            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Select Spreadsheet File *</label>
                <div class="border-2 border-dashed border-slate-800 hover:border-blue-500/60 rounded-2xl p-8 text-center bg-slate-950/40 transition">
                    <i class="fa-solid fa-file-excel text-4xl text-emerald-400 mb-3 block"></i>
                    <p class="text-sm font-semibold text-white">Click to select or drag & drop file here</p>
                    <p class="text-xs text-slate-500 mt-1">Supports XLSX, XLS, CSV (Max file size: 10MB)</p>
                    
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="mt-4 block w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                </div>
                @error('file') <span class="text-xs text-rose-400 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Action Button -->
            <div class="flex items-center justify-end pt-4 border-t border-slate-800">
                <button type="submit" :disabled="isSubmitting" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-blue-600/25 transition flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i :class="isSubmitting ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-magnifying-glass-chart'"></i>
                    <span x-text="isSubmitting ? 'Processing & Validating Spreadsheet...' : 'Upload & Preview Data'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Recent Import Batches Preview -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center space-x-2">
            <i class="fa-solid fa-clock-rotate-left text-blue-400"></i>
            <span>Recent Import Batches</span>
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400">
                        <th class="py-2.5 px-3">Batch ID</th>
                        <th class="py-2.5 px-3">Filename</th>
                        <th class="py-2.5 px-3">User</th>
                        <th class="py-2.5 px-3 text-center">Inserted</th>
                        <th class="py-2.5 px-3 text-center">Updated</th>
                        <th class="py-2.5 px-3 text-center">Failed</th>
                        <th class="py-2.5 px-3 text-right">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentBatches as $batch)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="py-2.5 px-3 font-mono font-bold text-blue-400">#{{ $batch->id }}</td>
                        <td class="py-2.5 px-3 font-bold text-white">{{ $batch->original_filename }}</td>
                        <td class="py-2.5 px-3 text-slate-400">{{ $batch->user->name ?? 'System' }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-emerald-400">{{ $batch->inserted_rows }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-indigo-400">{{ $batch->updated_rows }}</td>
                        <td class="py-2.5 px-3 text-center font-bold {{ $batch->failed_rows > 0 ? 'text-rose-400' : 'text-slate-500' }}">{{ $batch->failed_rows }}</td>
                        <td class="py-2.5 px-3 text-right text-slate-400">{{ $batch->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-4 text-center text-slate-500">No import history found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
