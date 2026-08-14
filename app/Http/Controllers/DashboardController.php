<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request)
    {
        $preset = $request->input('preset', 'all');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($preset !== 'custom' && $preset !== 'all') {
            match ($preset) {
                'today' => [
                    $fromDate = Carbon::today()->toDateString(),
                    $toDate = Carbon::today()->toDateString()
                ],
                'yesterday' => [
                    $fromDate = Carbon::yesterday()->toDateString(),
                    $toDate = Carbon::yesterday()->toDateString()
                ],
                'this_week' => [
                    $fromDate = Carbon::now()->startOfWeek()->toDateString(),
                    $toDate = Carbon::now()->endOfWeek()->toDateString()
                ],
                'this_month' => [
                    $fromDate = Carbon::now()->startOfMonth()->toDateString(),
                    $toDate = Carbon::now()->endOfMonth()->toDateString()
                ],
                'last_month' => [
                    $fromDate = Carbon::now()->subMonth()->startOfMonth()->toDateString(),
                    $toDate = Carbon::now()->subMonth()->endOfMonth()->toDateString()
                ],
                'this_year' => [
                    $fromDate = Carbon::now()->startOfYear()->toDateString(),
                    $toDate = Carbon::now()->endOfYear()->toDateString()
                ],
                default => null,
            };
        }

        $stats = $this->reportService->getDashboardStats($fromDate, $toDate);
        $chartData = $this->reportService->getChartData($fromDate, $toDate);
        $topOrders = $this->reportService->getTopProfitableOrders(10, $fromDate, $toDate);
        $lowRoiOrders = $this->reportService->getLowRoiOrders(0.10, 10, $fromDate, $toDate);

        return view('dashboard', compact('stats', 'chartData', 'topOrders', 'lowRoiOrders', 'preset', 'fromDate', 'toDate'));
    }
}
