<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportExcelRequest;
use App\Models\ImportBatch;
use App\Services\OrderImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportController extends Controller
{
    protected OrderImportService $importService;

    public function __construct(OrderImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        if (!auth()->user()?->hasPermission('nav_import')) {
            abort(403, 'Access denied. You do not have permission to access Excel Import.');
        }

        $recentBatches = ImportBatch::with('user')->orderByDesc('created_at')->limit(10)->get();
        return view('import.index', compact('recentBatches'));
    }

    public function preview(ImportExcelRequest $request)
    {
        $file = $request->file('file');
        $mode = $request->input('mode', 'create');

        $tempPath = $file->storeAs('imports/temp', uniqid('import_') . '.' . $file->getClientOriginalExtension(), 'local');
        $fullPath = Storage::disk('local')->path($tempPath);

        if (!file_exists($fullPath)) {
            return redirect()->back()->withErrors(['file' => "Uploaded file could not be read at path: {$fullPath}"]);
        }

        try {
            $preview = $this->importService->previewFile($fullPath);
        } catch (\Exception $e) {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            return redirect()->back()->withErrors(['file' => 'Import Preview Error: ' . $e->getMessage()]);
        }

        session([
            'import_file_path' => $fullPath,
            'import_original_filename' => $file->getClientOriginalName(),
            'import_mode' => $mode,
            'import_preview' => $preview,
        ]);

        return view('import.preview', compact('preview', 'mode'));
    }

    public function confirmImport(Request $request)
    {
        $preview = session('import_preview');
        $mode = session('import_mode', 'create');
        $originalFilename = session('import_original_filename', 'uploaded_file.xlsx');

        if (!$preview) {
            return redirect()->route('import.index')->with('error', 'Import session expired. Please upload the file again.');
        }

        try {
            $batch = $this->importService->processImport($preview, $mode, $originalFilename);

            // Clean up session
            $filePath = session('import_file_path');
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
            session()->forget(['import_file_path', 'import_original_filename', 'import_mode', 'import_preview']);

            return redirect()->route('import.history')->with('success', "Import batch #{$batch->id} completed! Inserted: {$batch->inserted_rows}, Updated: {$batch->updated_rows}, Skipped: {$batch->skipped_rows}, Failed: {$batch->failed_rows}.");
        } catch (\Exception $e) {
            return redirect()->route('import.index')->with('error', 'Error processing import: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $batches = ImportBatch::with('user')->orderByDesc('created_at')->paginate(15);
        return view('import.history', compact('batches'));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Order Import Template');

        // Input Headers (matching Order Entry form, excluding auto-calculated fields)
        $headers = [
            'Date',
            'EBAY-ORDER',
            "PRICE (Customer's Price)",
            'Ad Fee Charges',
            'AMAZON-ORDER',
            'PRICE (My Payments)',
            'Tracking Number',
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Header Styling
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Dark Slate
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Sample rows with realistic data
        $sampleRows = [
            ['2026-08-12', '27-14904-69608', '15.00', '1.50', '304-9841814-4365162', '8.00', '9400111899562912345678'],
            ['2026-08-12', '12-14930-22788', '25.50', '2.50', '304-7985807-2664352', '14.00', '9400111899562912345679'],
            ['2026-08-13', '24-14929-51773', '49.99', '5.00', '304-0286337-2817948', '32.00', ''],
            ['2026-08-13', '14-14934-05410', '19.99', '2.00', '304-1303640-6432329', '11.50', ''],
        ];

        $sheet->fromArray($sampleRows, null, 'A2');

        // Auto column widths
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'Order_Import_Template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadErrors(ImportBatch $batch)
    {
        if (empty($batch->error_summary)) {
            return redirect()->back()->with('info', 'No error logs recorded for this import batch.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Errors');

        $sheet->fromArray([['Row Number', 'eBay Order Number', 'Validation Error']], null, 'A1');

        $rows = [];
        foreach ($batch->error_summary as $err) {
            $rows[] = [
                $err['row'] ?? '',
                $err['ebay_order'] ?? '',
                $err['errors'] ?? '',
            ];
        }

        $sheet->fromArray($rows, null, 'A2');

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, "Import_Errors_Batch_{$batch->id}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
