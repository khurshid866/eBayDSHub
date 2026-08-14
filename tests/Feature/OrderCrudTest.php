<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected Company $companyA;
    protected Company $companyB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $this->companyA = Company::create(['name' => 'Company A', 'code' => 'comp-a-' . uniqid()]);
        $this->companyB = Company::create(['name' => 'Company B', 'code' => 'comp-b-' . uniqid()]);

        $this->userA = User::create([
            'company_id' => $this->companyA->id,
            'name' => 'User A',
            'email' => 'usera_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        $this->userB = User::create([
            'company_id' => $this->companyB->id,
            'name' => 'User B',
            'email' => 'userb_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);
    }

    public function test_authenticated_user_can_view_orders_index(): void
    {
        $response = $this->actingAs($this->userA)->get('/orders');
        $response->assertStatus(200);
    }

    public function test_can_create_order_with_ad_fee_charges_and_auto_calculations(): void
    {
        $uniqueEbay = '77' . rand(10000, 99999) . rand(10000, 99999);

        $payload = [
            'order_date' => '2026-08-12',
            'ebay_order_number' => $uniqueEbay,
            'customer_price' => 10.00,
            'ad_fee_charges' => 1.50,
            'amazon_order_number' => '30498418144365162',
            'supplier_cost' => 5.00,
            'status' => 'Completed',
            'notes' => 'Test order with ad fee charges',
        ];

        $response = $this->actingAs($this->userA)->post('/orders', $payload);

        $response->assertRedirect('/orders');

        $expectedFormattedEbay = substr($uniqueEbay, 0, 2) . '-' . substr($uniqueEbay, 2, 5) . '-' . substr($uniqueEbay, 7, 5);

        // E_NET = 10.00 - 1.50 = 8.50, Profit = 8.50 - 5.00 = 3.50, ROI = 3.50 / 8.50 = 0.4118
        $this->assertDatabaseHas('orders', [
            'company_id' => $this->companyA->id,
            'ebay_order_number' => $expectedFormattedEbay,
            'customer_price' => 10.00,
            'ad_fee_charges' => 1.50,
            'ebay_net' => 8.50,
            'profit' => 3.50,
        ]);
    }

    public function test_company_data_isolation(): void
    {
        $orderA = Order::create([
            'company_id' => $this->companyA->id,
            'order_date' => '2026-08-12',
            'ebay_order_number' => '11-11111-11111',
            'amazon_order_number' => '304-9841814-4365162',
            'customer_price' => 10.00,
            'ad_fee_charges' => 0.00,
            'supplier_cost' => 5.00,
            'ebay_net' => 10.00,
            'profit' => 5.00,
            'roi' => 0.50,
            'status' => 'Completed',
        ]);

        $orderB = Order::create([
            'company_id' => $this->companyB->id,
            'order_date' => '2026-08-12',
            'ebay_order_number' => '22-22222-22222',
            'amazon_order_number' => '304-9841814-4365163',
            'customer_price' => 20.00,
            'ad_fee_charges' => 0.00,
            'supplier_cost' => 10.00,
            'ebay_net' => 20.00,
            'profit' => 10.00,
            'roi' => 0.50,
            'status' => 'Completed',
        ]);

        // User A should see Order A but NOT Order B
        $responseA = $this->actingAs($this->userA)->get('/orders');
        $responseA->assertSee('11-11111-11111');
        $responseA->assertDontSee('22-22222-22222');

        // User B should see Order B but NOT Order A
        $responseB = $this->actingAs($this->userB)->get('/orders');
        $responseB->assertSee('22-22222-22222');
        $responseB->assertDontSee('11-11111-11111');
    }

    public function test_authenticated_user_can_show_order_details(): void
    {
        $order = Order::create([
            'company_id' => $this->companyA->id,
            'order_date' => '2026-08-12',
            'ebay_order_number' => '33-33333-33333',
            'amazon_order_number' => '304-9841814-4365164',
            'customer_price' => 30.00,
            'ad_fee_charges' => 0.00,
            'supplier_cost' => 15.00,
            'ebay_net' => 30.00,
            'profit' => 15.00,
            'roi' => 0.50,
            'status' => 'Completed',
        ]);

        $response = $this->actingAs($this->userA)->get("/orders/{$order->id}");
        $response->assertStatus(200);
    }

    public function test_download_sample_excel_template(): void
    {
        $response = $this->actingAs($this->userA)->get('/import/template');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
