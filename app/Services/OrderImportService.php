<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\Order;
use App\Services\AuditLogService;
use App\Services\OrderCalculationService;
use App\Services\OrderNumberFormatterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;

class OrderImportService
{
    protected OrderNumberFormatterService $formatter;
    protected OrderCalculationService $calculator;
    protected AuditLogService $auditLogger;

    public function __construct(
        OrderNumberFormatterService $formatter,
        OrderCalculationService $calculator,
        AuditLogService $auditLogger
    ) {
        $this->formatter = $formatter;
        $this->calculator = $calculator;
        $this->auditLogger = $auditLogger;
    }

    /**
     * Parse Excel/CSV file and generate preview structure.
     */
    public function previewFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        if (empty($rows)) {
            throw new \Exception("Uploaded spreadsheet is empty.");
        }

        // Find header row
        $headerRowIndex = null;
        $mappedHeaders = [];

        foreach ($rows as $rowIndex => $row) {
            $mapped = $this->detectHeaders($row);
            if (!empty($mapped['ebay_order_number'])) {
                $headerRowIndex = $rowIndex;
                $mappedHeaders = $mapped;
                break;
            }
        }

        if ($headerRowIndex === null) {
            throw new \Exception("Could not detect required Excel header columns. Please check column titles.");
        }

        $previewRows = [];
        $totalRows = 0;
        $newCount = 0;
        $existingCount = 0;
        $invalidCount = 0;
        $duplicateInFileCount = 0;

        $seenEbayOrders = [];
        $companyId = Auth::user()?->company_id;
        $query = Order::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $existingEbayOrders = $query->pluck('ebay_order_number')->toArray();
        $existingMap = array_fill_keys($existingEbayOrders, true);

        $lastValidDate = null;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= $headerRowIndex) {
                continue;
            }

            $rawDate = trim($row[$mappedHeaders['order_date'] ?? 'A'] ?? '');
            $rawEbay = trim($row[$mappedHeaders['ebay_order_number'] ?? 'B'] ?? '');
            $rawTracking = isset($mappedHeaders['ebay_tracking_number']) ? trim($row[$mappedHeaders['ebay_tracking_number']] ?? '') : '';
            $rawCustPrice = isset($mappedHeaders['customer_price']) ? trim($row[$mappedHeaders['customer_price']] ?? '') : (isset($row['C']) ? trim($row['C']) : '');
            $rawAdFee = isset($mappedHeaders['ad_fee_charges']) ? trim($row[$mappedHeaders['ad_fee_charges']] ?? '0') : '0';
            $rawAmazon = isset($mappedHeaders['amazon_order_number']) ? trim($row[$mappedHeaders['amazon_order_number']] ?? '') : (isset($row['D']) ? trim($row['D']) : '');
            $rawSupplierCost = isset($mappedHeaders['supplier_cost']) ? trim($row[$mappedHeaders['supplier_cost']] ?? '0') : (isset($row['E']) ? trim($row['E']) : '0');
            $rawEbayNet = isset($mappedHeaders['ebay_net']) ? trim($row[$mappedHeaders['ebay_net']] ?? '') : '';

            // Skip completely empty or summary total rows
            if (empty($rawEbay) && empty($rawDate) && empty($rawCustPrice)) {
                continue;
            }

            if (stripos($rawEbay, 'total') !== false || stripos($rawDate, 'total') !== false) {
                continue;
            }

            $totalRows++;

            $formattedEbay = $this->formatter->formatEbayOrderNumber($rawEbay);
            $formattedAmazon = $this->formatter->formatAmazonOrderNumber($rawAmazon);

            $parsedDate = $this->parseExcelDate($rawDate);
            if (!$parsedDate && !empty($lastValidDate) && empty($rawDate)) {
                $parsedDate = $lastValidDate;
            } elseif ($parsedDate) {
                $lastValidDate = $parsedDate;
            }

            $custPrice = $this->parseNumeric($rawCustPrice);
            $adFee = $this->parseNumeric($rawAdFee) ?? 0.0;
            $supplierCost = $this->parseNumeric($rawSupplierCost);
            $ebayNet = $this->parseNumeric($rawEbayNet);

            if ($ebayNet === null && $custPrice !== null) {
                $ebayNet = $this->calculator->calculateEbayNet($custPrice, $adFee);
            } elseif ($ebayNet !== null && $custPrice !== null && empty($rawAdFee)) {
                $adFee = max(0.0, round($custPrice - $ebayNet, 2));
            }

            $errors = [];

            if (empty($formattedEbay) || !$this->formatter->isValidEbayOrderNumber($formattedEbay)) {
                $errors[] = "Invalid eBay order number format: '$rawEbay'";
            }

            if (!$parsedDate) {
                $errors[] = "Invalid order date: '$rawDate'";
            }

            if ($custPrice === null || $custPrice < 0) {
                $errors[] = "Invalid customer price";
            }

            if ($supplierCost === null || $supplierCost < 0) {
                $errors[] = "Invalid supplier cost";
            }

            if ($ebayNet === null || $ebayNet < 0) {
                $errors[] = "Invalid eBay Net amount";
            }

            $status = 'new';

            if (!empty($errors)) {
                $status = 'invalid';
                $invalidCount++;
            } elseif (isset($seenEbayOrders[$formattedEbay])) {
                $status = 'duplicate_in_file';
                $duplicateInFileCount++;
                $errors[] = "Duplicate eBay order number in file";
            } elseif (isset($existingMap[$formattedEbay])) {
                $status = 'existing';
                $existingCount++;
            } else {
                $newCount++;
                $seenEbayOrders[$formattedEbay] = true;
            }

            $calculatedProfit = ($ebayNet !== null && $supplierCost !== null)
                ? $this->calculator->calculateProfit($ebayNet, $supplierCost)
                : 0.0;

            $calculatedRoi = ($ebayNet !== null)
                ? $this->calculator->calculateRoi($calculatedProfit, $ebayNet)
                : 0.0;

            $previewRows[] = [
                'row_index' => $rowIndex,
                'raw_date' => $rawDate,
                'order_date' => $parsedDate,
                'raw_ebay' => $rawEbay,
                'ebay_order_number' => $formattedEbay,
                'ebay_tracking_number' => $rawTracking,
                'customer_price' => $custPrice ?? 0.0,
                'ad_fee_charges' => $adFee,
                'raw_amazon' => $rawAmazon,
                'amazon_order_number' => $formattedAmazon,
                'supplier_cost' => $supplierCost ?? 0.0,
                'ebay_net' => $ebayNet ?? 0.0,
                'profit' => $calculatedProfit,
                'roi' => $calculatedRoi,
                'status' => $status,
                'errors' => $errors,
            ];
        }

        return [
            'header_row' => $headerRowIndex,
            'mapped_headers' => $mappedHeaders,
            'total_rows' => $totalRows,
            'new_count' => $newCount,
            'existing_count' => $existingCount,
            'invalid_count' => $invalidCount,
            'duplicate_in_file_count' => $duplicateInFileCount,
            'rows' => $previewRows,
        ];
    }

    /**
     * Process actual import with transactional batch database update.
     */
    public function processImport(array $previewData, string $mode, string $originalFilename): ImportBatch
    {
        $user = Auth::user();
        $companyId = $user->company_id ?: Company::first()?->id;

        $batch = ImportBatch::create([
            'company_id' => $companyId,
            'original_filename' => $originalFilename,
            'file_type' => pathinfo($originalFilename, PATHINFO_EXTENSION),
            'total_rows' => $previewData['total_rows'],
            'inserted_rows' => 0,
            'updated_rows' => 0,
            'skipped_rows' => 0,
            'failed_rows' => 0,
            'imported_by' => $user->id,
            'started_at' => now(),
            'status' => 'processing',
        ]);

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $errorLogs = [];

        DB::transaction(function () use ($previewData, $mode, $batch, $companyId, $user, &$inserted, &$updated, &$skipped, &$failed, &$errorLogs) {
            foreach ($previewData['rows'] as $row) {
                if ($row['status'] === 'invalid' || $row['status'] === 'duplicate_in_file') {
                    $failed++;
                    $errorLogs[] = [
                        'row' => $row['row_index'],
                        'ebay_order' => $row['raw_ebay'],
                        'errors' => implode(', ', $row['errors']),
                    ];
                    continue;
                }

                $existingOrder = Order::where('company_id', $companyId)
                    ->where('ebay_order_number', $row['ebay_order_number'])
                    ->first();

                if ($existingOrder) {
                    if ($mode === 'update') {
                        $oldValues = $existingOrder->toArray();

                        $updateData = [
                            'order_date' => $row['order_date'],
                            'customer_price' => $row['customer_price'],
                            'ad_fee_charges' => $row['ad_fee_charges'],
                            'amazon_order_number' => $row['amazon_order_number'],
                            'supplier_cost' => $row['supplier_cost'],
                            'ebay_net' => $row['ebay_net'],
                            'profit' => $row['profit'],
                            'roi' => $row['roi'],
                            'updated_by' => $user->id,
                        ];

                        if (!empty($row['ebay_tracking_number'])) {
                            $updateData['ebay_tracking_number'] = $row['ebay_tracking_number'];
                        }

                        $existingOrder->update($updateData);

                        $updated++;

                        $this->auditLogger->log(
                            'updated_order_via_import',
                            Order::class,
                            $existingOrder->id,
                            $oldValues,
                            $existingOrder->toArray()
                        );
                    } else {
                        // Skip duplicate entry
                        $skipped++;
                    }
                } else {
                    $newOrder = Order::create([
                        'company_id' => $companyId,
                        'order_date' => $row['order_date'],
                        'ebay_order_number' => $row['ebay_order_number'],
                        'ebay_tracking_number' => $row['ebay_tracking_number'] ?? null,
                        'customer_price' => $row['customer_price'],
                        'ad_fee_charges' => $row['ad_fee_charges'],
                        'amazon_order_number' => $row['amazon_order_number'],
                        'supplier_cost' => $row['supplier_cost'],
                        'ebay_net' => $row['ebay_net'],
                        'profit' => $row['profit'],
                        'roi' => $row['roi'],
                        'status' => 'Completed',
                        'created_by' => $user->id,
                    ]);

                    $inserted++;

                    $this->auditLogger->log(
                        'created_order_via_import',
                        Order::class,
                        $newOrder->id,
                        null,
                        $newOrder->toArray()
                    );
                }
            }

            $batch->update([
                'inserted_rows' => $inserted,
                'updated_rows' => $updated,
                'skipped_rows' => $skipped,
                'failed_rows' => $failed,
                'completed_at' => now(),
                'status' => 'completed',
                'error_summary' => $errorLogs,
            ]);
        });

        return $batch;
    }

    /**
     * Parse numeric values supporting both dot and comma decimal separators and currency symbols.
     */
    protected function parseNumeric(?string $val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }

        $cleaned = trim((string)$val);
        $cleaned = str_replace(['$', '€', '£', ' ', '%'], '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);

        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    /**
     * Flexible column header detection including eBay Tracking Number.
     */
    protected function detectHeaders(array $row): array
    {
        $mapped = [];

        foreach ($row as $colLetter => $cellValue) {
            if (empty($cellValue)) continue;

            $val = strtolower(trim((string)$cellValue));

            if (str_contains($val, 'tracking')) {
                $mapped['ebay_tracking_number'] = $colLetter;
            } elseif (str_contains($val, 'ad fee') || str_contains($val, 'ad_fee') || str_contains($val, 'advertising')) {
                $mapped['ad_fee_charges'] = $colLetter;
            } elseif (str_contains($val, 'date') && !isset($mapped['order_date'])) {
                $mapped['order_date'] = $colLetter;
            } elseif ((str_contains($val, 'ebay') || str_contains($val, 'order #') || str_contains($val, 'order_id')) && !isset($mapped['ebay_order_number'])) {
                $mapped['ebay_order_number'] = $colLetter;
            } elseif ((str_contains($val, 'customer') || str_contains($val, 'cust') || str_contains($val, 'sale price')) && !isset($mapped['customer_price'])) {
                $mapped['customer_price'] = $colLetter;
            } elseif (str_contains($val, 'amazon') && !isset($mapped['amazon_order_number'])) {
                $mapped['amazon_order_number'] = $colLetter;
            } elseif ((str_contains($val, 'payment') || str_contains($val, 'supplier') || str_contains($val, 'my payment') || str_contains($val, 'amazon price')) && !isset($mapped['supplier_cost'])) {
                $mapped['supplier_cost'] = $colLetter;
            } elseif (str_contains($val, 'net') && !isset($mapped['ebay_net'])) {
                $mapped['ebay_net'] = $colLetter;
            }
        }

        // Default column fallback if date column wasn't explicitly named in header row
        if (!isset($mapped['order_date']) && isset($mapped['ebay_order_number']) && $mapped['ebay_order_number'] === 'B') {
            $mapped['order_date'] = 'A';
        }

        return $mapped;
    }

    /**
     * Parse date values in various Excel formats.
     */
    public function parseExcelDate(?string $value): ?string
    {
        if (empty($value)) return null;

        $trimmed = trim($value);

        if (is_numeric($trimmed) && (float)$trimmed > 30000) {
            try {
                $dateTime = PhpSpreadsheetDate::excelToDateTimeObject((float)$trimmed);
                return $dateTime->format('Y-m-d');
            } catch (\Exception $e) {
                // fallback
            }
        }

        try {
            $cleaned = str_replace(['.', '/'], [' ', '-'], $trimmed);
            return Carbon::parse($cleaned)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
