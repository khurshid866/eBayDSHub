<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get aggregate KPI metrics for dashboard with optional date filter.
     */
    public function getDashboardStats(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = Order::query()->filterByDate($fromDate, $toDate);

        $totalOrders = (clone $query)->count();
        $customerRevenue = (float) ((clone $query)->sum('customer_price') ?? 0);
        $supplierCost = (float) ((clone $query)->sum('supplier_cost') ?? 0);
        $totalEbayNet = (float) ((clone $query)->sum('ebay_net') ?? 0);
        $totalProfit = (float) ((clone $query)->sum('profit') ?? 0);

        // Weighted ROI: Total Profit / Total E_NET
        $averageRoi = $totalEbayNet > 0 ? ($totalProfit / $totalEbayNet) : 0;

        $completedOrders = (clone $query)->where('status', 'Completed')->count();
        $pendingOrders = (clone $query)->where('status', 'Pending')->count();
        $cancelledOrders = (clone $query)->where('status', 'Cancelled')->count();

        // ROI Threshold from settings
        $roiThreshold = Setting::get('roi_warning_threshold', 10.0) / 100;

        // Business Insights
        $avgProfitPerOrder = $totalOrders > 0 ? ($totalProfit / $totalOrders) : 0;

        $highestProfitOrder = (clone $query)->orderByDesc('profit')->first();

        // Best performing day
        $bestDayRow = (clone $query)
            ->select('order_date', DB::raw('SUM(profit) as total_day_profit'))
            ->groupBy('order_date')
            ->orderByDesc('total_day_profit')
            ->first();

        $bestPerformingDay = $bestDayRow ? [
            'date' => Carbon::parse($bestDayRow->order_date)->format('M d, Y'),
            'profit' => (float) $bestDayRow->total_day_profit,
        ] : null;

        // Month-over-Month comparison
        $thisMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $thisMonthEnd = Carbon::now()->endOfMonth()->toDateString();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $ordersThisMonth = Order::whereBetween('order_date', [$thisMonthStart, $thisMonthEnd])->count();
        $profitThisMonth = (float) Order::whereBetween('order_date', [$thisMonthStart, $thisMonthEnd])->sum('profit');
        $profitLastMonth = (float) Order::whereBetween('order_date', [$lastMonthStart, $lastMonthEnd])->sum('profit');

        $momProfitChange = $profitLastMonth > 0
            ? (($profitThisMonth - $profitLastMonth) / $profitLastMonth) * 100
            : ($profitThisMonth > 0 ? 100 : 0);

        $lowRoiOrdersCount = (clone $query)->where('roi', '<', $roiThreshold)->count();
        $negativeProfitOrdersCount = (clone $query)->where('profit', '<=', 0)->count();

        return [
            'total_orders' => $totalOrders,
            'customer_revenue' => $customerRevenue,
            'supplier_cost' => $supplierCost,
            'total_ebay_net' => $totalEbayNet,
            'total_profit' => $totalProfit,
            'average_roi' => $averageRoi,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'insights' => [
                'avg_profit_per_order' => $avgProfitPerOrder,
                'highest_profit_order' => $highestProfitOrder,
                'best_performing_day' => $bestPerformingDay,
                'orders_this_month' => $ordersThisMonth,
                'profit_this_month' => $profitThisMonth,
                'profit_last_month' => $profitLastMonth,
                'mom_profit_change' => $momProfitChange,
                'low_roi_orders_count' => $lowRoiOrdersCount,
                'negative_profit_orders_count' => $negativeProfitOrdersCount,
            ]
        ];
    }

    /**
     * Get chart dataset formatting for Dashboard.
     */
    public function getChartData(?string $fromDate = null, ?string $toDate = null): array
    {
        $query = Order::query()->filterByDate($fromDate, $toDate);

        // Profit by Date
        $profitByDate = (clone $query)
            ->select('order_date', DB::raw('SUM(profit) as total_profit'), DB::raw('SUM(customer_price) as total_revenue'), DB::raw('SUM(supplier_cost) as total_cost'))
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        $dates = [];
        $profits = [];
        $revenues = [];
        $costs = [];

        foreach ($profitByDate as $row) {
            $dates[] = Carbon::parse($row->order_date)->format('M d');
            $profits[] = (float) $row->total_profit;
            $revenues[] = (float) $row->total_revenue;
            $costs[] = (float) $row->total_cost;
        }

        // Orders by Status
        $ordersByStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // ROI Distribution
        $roiTrend = (clone $query)
            ->select('order_date', DB::raw('AVG(roi) * 100 as avg_roi'))
            ->groupBy('order_date')
            ->orderBy('order_date')
            ->get();

        $roiDates = [];
        $roiValues = [];

        foreach ($roiTrend as $row) {
            $roiDates[] = Carbon::parse($row->order_date)->format('M d');
            $roiValues[] = round((float) $row->avg_roi, 2);
        }

        return [
            'profit_by_date' => [
                'labels' => $dates,
                'profits' => $profits,
                'revenues' => $revenues,
                'costs' => $costs,
            ],
            'orders_by_status' => [
                'labels' => array_keys($ordersByStatus),
                'values' => array_values($ordersByStatus),
            ],
            'roi_trend' => [
                'labels' => $roiDates,
                'values' => $roiValues,
            ]
        ];
    }

    /**
     * Get top profitable orders.
     */
    public function getTopProfitableOrders(int $limit = 10, ?string $fromDate = null, ?string $toDate = null)
    {
        return Order::query()
            ->filterByDate($fromDate, $toDate)
            ->orderByDesc('profit')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low ROI orders below threshold.
     */
    public function getLowRoiOrders(float $threshold = 0.10, int $limit = 10, ?string $fromDate = null, ?string $toDate = null)
    {
        return Order::query()
            ->filterByDate($fromDate, $toDate)
            ->where('roi', '<', $threshold)
            ->orderBy('roi')
            ->limit($limit)
            ->get();
    }

    /**
     * Get filtered report records query builder.
     */
    public function getFilteredReportQuery(array $filters)
    {
        $query = Order::query();

        if (!empty($filters['from_date'])) {
            $query->whereDate('order_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('order_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['min_profit']) && $filters['min_profit'] !== '') {
            $query->where('profit', '>=', (float)$filters['min_profit']);
        }
        if (isset($filters['max_profit']) && $filters['max_profit'] !== '') {
            $query->where('profit', '<=', (float)$filters['max_profit']);
        }
        if (isset($filters['min_roi']) && $filters['min_roi'] !== '') {
            $query->where('roi', '>=', (float)$filters['min_roi'] / 100);
        }
        if (isset($filters['max_roi']) && $filters['max_roi'] !== '') {
            $query->where('roi', '<=', (float)$filters['max_roi'] / 100);
        }

        return $query;
    }
}
