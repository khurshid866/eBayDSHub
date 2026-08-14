<?php

namespace App\Http\Controllers;

use App\Services\OrderCalculationService;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    protected ReportService $reportService;
    protected OrderCalculationService $calculator;

    public function __construct(ReportService $reportService, OrderCalculationService $calculator)
    {
        $this->reportService = $reportService;
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        if (!auth()->user()?->hasPermission('nav_reports')) {
            abort(403, 'Access denied. You do not have permission to access Reports.');
        }

        $filters = [
            'report_type' => $request->input('report_type', 'sales'),
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date'),
            'status' => $request->input('status', 'all'),
            'min_profit' => $request->input('min_profit'),
            'max_profit' => $request->input('max_profit'),
            'min_roi' => $request->input('min_roi'),
            'max_roi' => $request->input('max_roi'),
        ];

        $query = $this->reportService->getFilteredReportQuery($filters);

        $summary = [
            'total_orders' => (clone $query)->count(),
            'customer_revenue' => (float) ((clone $query)->sum('customer_price') ?? 0),
            'ad_fee_charges' => (float) ((clone $query)->sum('ad_fee_charges') ?? 0),
            'supplier_cost' => (float) ((clone $query)->sum('supplier_cost') ?? 0),
            'ebay_net' => (float) ((clone $query)->sum('ebay_net') ?? 0),
            'total_profit' => (float) ((clone $query)->sum('profit') ?? 0),
        ];

        $summary['avg_roi'] = $summary['ebay_net'] > 0 ? ($summary['total_profit'] / $summary['ebay_net']) : 0;

        $orders = $query->orderBy('order_date', 'desc')->paginate(20)->withQueryString();

        return view('reports.index', compact('orders', 'summary', 'filters'));
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()?->hasPermission('action_export_reports')) {
            abort(403, 'Access denied. You do not have permission to export reports.');
        }

        $filters = $request->all();
        $orders = $this->reportService->getFilteredReportQuery($filters)->orderBy('order_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Order Profit Report');

        // Header Row
        $headers = ['Order Date', 'eBay Order Number', 'Customer Price', 'Ad Fee Charges', 'Amazon Order Number', 'Supplier Cost', 'E_NET', 'Profit', 'ROI (%)', 'Status'];
        $sheet->fromArray([$headers], null, 'A1');

        $rowNum = 2;
        $totCust = 0;
        $totAdFee = 0;
        $totSupp = 0;
        $totEbay = 0;
        $totProf = 0;

        foreach ($orders as $order) {
            $sheet->setCellValue("A{$rowNum}", $order->order_date ? $order->order_date->format('Y-m-d') : '');
            $sheet->setCellValue("B{$rowNum}", $order->ebay_order_number);
            $sheet->setCellValue("C{$rowNum}", (float)$order->customer_price);
            $sheet->setCellValue("D{$rowNum}", (float)$order->ad_fee_charges);
            $sheet->setCellValue("E{$rowNum}", $order->amazon_order_number);
            $sheet->setCellValue("F{$rowNum}", (float)$order->supplier_cost);
            $sheet->setCellValue("G{$rowNum}", (float)$order->ebay_net);
            $sheet->setCellValue("H{$rowNum}", (float)$order->profit);
            $sheet->setCellValue("I{$rowNum}", number_format($order->roi * 100, 2) . '%');
            $sheet->setCellValue("J{$rowNum}", $order->status);

            $totCust += (float)$order->customer_price;
            $totAdFee += (float)$order->ad_fee_charges;
            $totSupp += (float)$order->supplier_cost;
            $totEbay += (float)$order->ebay_net;
            $totProf += (float)$order->profit;

            $rowNum++;
        }

        // Summary Total Row
        $sheet->setCellValue("A{$rowNum}", 'TOTALS');
        $sheet->setCellValue("C{$rowNum}", $totCust);
        $sheet->setCellValue("D{$rowNum}", $totAdFee);
        $sheet->setCellValue("F{$rowNum}", $totSupp);
        $sheet->setCellValue("G{$rowNum}", $totEbay);
        $sheet->setCellValue("H{$rowNum}", $totProf);
        $avgRoi = $totEbay > 0 ? ($totProf / $totEbay) : 0;
        $sheet->setCellValue("I{$rowNum}", number_format($avgRoi * 100, 2) . '%');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'eBay_Dropshipping_Report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        if (!auth()->user()?->hasPermission('action_export_reports')) {
            abort(403, 'Access denied. You do not have permission to export reports.');
        }

        $filters = $request->all();
        $orders = $this->reportService->getFilteredReportQuery($filters)->orderBy('order_date', 'desc')->get();

        $summary = [
            'total_orders' => $orders->count(),
            'customer_revenue' => $orders->sum('customer_price'),
            'ad_fee_charges' => $orders->sum('ad_fee_charges'),
            'supplier_cost' => $orders->sum('supplier_cost'),
            'ebay_net' => $orders->sum('ebay_net'),
            'total_profit' => $orders->sum('profit'),
        ];
        $summary['avg_roi'] = $summary['ebay_net'] > 0 ? ($summary['total_profit'] / $summary['ebay_net']) : 0;

        $pdf = Pdf::loadView('reports.pdf.sales', compact('orders', 'summary', 'filters'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('eBay_Dropshipping_Report_' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        if (!auth()->user()?->hasPermission('action_export_reports')) {
            abort(403, 'Access denied. You do not have permission to export reports.');
        }

        $filters = $request->all();
        $orders = $this->reportService->getFilteredReportQuery($filters)->orderBy('order_date', 'desc')->get();

        $filename = 'eBay_Dropshipping_Report_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Order Date', 'eBay Order Number', 'Customer Price', 'Ad Fee Charges', 'Amazon Order Number', 'Supplier Cost', 'E_NET', 'Profit', 'ROI (%)', 'Status']);

        foreach ($orders as $order) {
            fputcsv($out, [
                $order->order_date ? $order->order_date->format('Y-m-d') : '',
                $order->ebay_order_number,
                $order->customer_price,
                $order->ad_fee_charges,
                $order->amazon_order_number,
                $order->supplier_cost,
                $order->ebay_net,
                $order->profit,
                number_format($order->roi * 100, 2) . '%',
                $order->status,
            ]);
        }

        fclose($out);
        exit;
    }
}
