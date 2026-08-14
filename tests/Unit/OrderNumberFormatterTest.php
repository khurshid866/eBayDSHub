<?php

namespace Tests\Unit;

use App\Services\OrderNumberFormatterService;
use PHPUnit\Framework\TestCase;

class OrderNumberFormatterTest extends TestCase
{
    protected OrderNumberFormatterService $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new OrderNumberFormatterService();
    }

    public function test_ebay_order_formatting_from_unformatted_digits(): void
    {
        $input = '271490469608';
        $expected = '27-14904-69608';
        $result = $this->formatter->formatEbayOrderNumber($input);

        $this->assertEquals($expected, $result);
        $this->assertTrue($this->formatter->isValidEbayOrderNumber($result));
    }

    public function test_ebay_order_formatting_preserves_already_formatted(): void
    {
        $input = '27-14904-69608';
        $result = $this->formatter->formatEbayOrderNumber($input);

        $this->assertEquals($input, $result);
        $this->assertTrue($this->formatter->isValidEbayOrderNumber($result));
    }

    public function test_ebay_order_formatting_normalizes_spaces(): void
    {
        $input = '27 14904 69608';
        $expected = '27-14904-69608';
        $result = $this->formatter->formatEbayOrderNumber($input);

        $this->assertEquals($expected, $result);
        $this->assertTrue($this->formatter->isValidEbayOrderNumber($result));
    }

    public function test_amazon_order_formatting_from_unformatted_digits(): void
    {
        $input = '30498418144365162';
        $expected = '304-9841814-4365162';
        $result = $this->formatter->formatAmazonOrderNumber($input);

        $this->assertEquals($expected, $result);
        $this->assertTrue($this->formatter->isValidAmazonOrderNumber($result));
    }
}
