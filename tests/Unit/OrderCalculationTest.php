<?php

namespace Tests\Unit;

use App\Services\OrderCalculationService;
use PHPUnit\Framework\TestCase;

class OrderCalculationTest extends TestCase
{
    protected OrderCalculationService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new OrderCalculationService();
    }

    public function test_ebay_net_calculation_with_ad_fee_charges(): void
    {
        // Customer Price = 10.00, Ad Fee = 1.50 -> E_NET = 8.50
        $ebayNet = $this->calculator->calculateEbayNet(10.00, 1.50);
        $this->assertEquals(8.50, $ebayNet);

        // Default Ad Fee = 0 -> E_NET = Customer Price
        $ebayNetDefault = $this->calculator->calculateEbayNet(6.99, 0.00);
        $this->assertEquals(6.99, $ebayNetDefault);
    }

    public function test_profit_calculation(): void
    {
        // E_NET = 3.49, Supplier Cost = 2.15 -> Profit = 1.34
        $profit = $this->calculator->calculateProfit(3.49, 2.15);
        $this->assertEquals(1.34, $profit);

        // E_NET = 6.74, Supplier Cost = 5.79 -> Profit = 0.95
        $profit2 = $this->calculator->calculateProfit(6.74, 5.79);
        $this->assertEquals(0.95, $profit2);
    }

    public function test_roi_calculation(): void
    {
        // Profit = 1.34, E_NET = 3.49 -> ROI = 0.3840 (38.40%)
        $roi = $this->calculator->calculateRoi(1.34, 3.49);
        $this->assertEquals(0.3840, $roi);

        $formatted = $this->calculator->formatPercentage($roi);
        $this->assertEquals('38.40%', $formatted);
    }

    public function test_division_by_zero_handling(): void
    {
        // E_NET = 0 -> ROI should be 0.0, not generate error
        $roi = $this->calculator->calculateRoi(1.50, 0.0);
        $this->assertEquals(0.0, $roi);

        $formatted = $this->calculator->formatPercentage($roi);
        $this->assertEquals('0.00%', $formatted);
    }
}
