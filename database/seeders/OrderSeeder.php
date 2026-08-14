<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderCalculationService;
use App\Services\OrderImportService;
use App\Services\OrderNumberFormatterService;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $excelPath = base_path('Book1.xlsx');

        if (!file_exists($excelPath)) {
            $this->command->warn("Book1.xlsx not found at {$excelPath}, skipping OrderSeeder.");
            return;
        }

        $defaultCompany = Company::where('code', 'default-enterprise-store')->first();
        $companyId = $defaultCompany ? $defaultCompany->id : 1;

        $admin = User::where('email', 'admin@ebay.com')->first();
        $adminId = $admin ? $admin->id : null;

        $formatter = app(OrderNumberFormatterService::class);
        $calculator = app(OrderCalculationService::class);
        $importService = app(OrderImportService::class);

        $spreadsheet = IOFactory::load($excelPath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray(null, true, true, true);

        $inserted = 0;

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= 2) {
                continue;
            }

            $rawDate = trim($row['A'] ?? '');
            $rawEbay = trim($row['B'] ?? '');
            $rawCustPrice = trim($row['C'] ?? '');
            $rawAmazon = trim($row['D'] ?? '');
            $rawSupplierCost = trim($row['E'] ?? '');
            $rawEbayNet = trim($row['F'] ?? '');

            if (empty($rawEbay) || empty($rawDate)) {
                continue;
            }

            $formattedEbay = $formatter->formatEbayOrderNumber($rawEbay);
            $formattedAmazon = $formatter->formatAmazonOrderNumber($rawAmazon);
            $parsedDate = $importService->parseExcelDate($rawDate);

            $custPrice = (float) $rawCustPrice;
            $supplierCost = (float) $rawSupplierCost;
            $ebayNet = (float) $rawEbayNet;
            $adFee = max(0.0, round($custPrice - $ebayNet, 2));

            $profit = $calculator->calculateProfit($ebayNet, $supplierCost);
            $roi = $calculator->calculateRoi($profit, $ebayNet);

            Order::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'ebay_order_number' => $formattedEbay,
                ],
                [
                    'order_date' => $parsedDate ?? '2026-07-23',
                    'customer_price' => $custPrice,
                    'ad_fee_charges' => $adFee,
                    'amazon_order_number' => $formattedAmazon,
                    'supplier_cost' => $supplierCost,
                    'ebay_net' => $ebayNet,
                    'profit' => $profit,
                    'roi' => $roi,
                    'status' => 'Completed',
                    'notes' => 'Imported from initial spreadsheet (Book1.xlsx)',
                    'created_by' => $adminId,
                ]
            );

            $inserted++;
        }

        $this->command->info("Seeded {$inserted} orders for Company ID {$companyId} successfully.");
    }
}
