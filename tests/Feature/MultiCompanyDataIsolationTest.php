<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiCompanyDataIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
    }

    public function test_strict_company_data_isolation_between_companies(): void
    {
        $companyA = Company::create(['name' => 'Company A', 'code' => 'comp-a-' . uniqid()]);
        $companyB = Company::create(['name' => 'Company B', 'code' => 'comp-b-' . uniqid()]);

        $superAdmin = User::create([
            'name' => 'Super Admin Iso',
            'email' => 'superiso_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
            'status' => 'active',
        ]);

        $userA = User::create([
            'company_id' => $companyA->id,
            'name' => 'User Company A',
            'email' => 'usera_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        $userB = User::create([
            'company_id' => $companyB->id,
            'name' => 'User Company B',
            'email' => 'userb_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        // Create Order for Company A
        $orderA = Order::create([
            'company_id' => $companyA->id,
            'order_date' => now()->toDateString(),
            'ebay_order_number' => '20-10000-00001',
            'customer_price' => 100.00,
            'ad_fee_charges' => 10.00,
            'amazon_order_number' => '111-0000001-0000001',
            'supplier_cost' => 50.00,
            'ebay_net' => 90.00,
            'profit' => 40.00,
            'roi' => 0.4444,
            'status' => 'Completed',
            'created_by' => $userA->id,
        ]);

        // Create Order for Company B
        $orderB = Order::create([
            'company_id' => $companyB->id,
            'order_date' => now()->toDateString(),
            'ebay_order_number' => '20-20000-00002',
            'customer_price' => 200.00,
            'ad_fee_charges' => 20.00,
            'amazon_order_number' => '222-0000002-0000002',
            'supplier_cost' => 80.00,
            'ebay_net' => 180.00,
            'profit' => 100.00,
            'roi' => 0.5555,
            'status' => 'Completed',
            'created_by' => $userB->id,
        ]);

        // 1. Super Admin can view both orders
        $this->actingAs($superAdmin)->get("/orders/{$orderA->id}")->assertStatus(200);
        $this->actingAs($superAdmin)->get("/orders/{$orderB->id}")->assertStatus(200);

        // 2. Company A user can view Order A, but receives 404 when accessing Order B
        $this->actingAs($userA)->get("/orders/{$orderA->id}")->assertStatus(200);
        $this->actingAs($userA)->get("/orders/{$orderB->id}")->assertStatus(404);

        // 3. Company B user can view Order B, but receives 404 when accessing Order A
        $this->actingAs($userB)->get("/orders/{$orderB->id}")->assertStatus(200);
        $this->actingAs($userB)->get("/orders/{$orderA->id}")->assertStatus(404);

        // 4. IDOR Protection: User A attempting to update Order B receives 404
        $this->actingAs($userA)->put("/orders/{$orderB->id}", [
            'order_date' => now()->toDateString(),
            'ebay_order_number' => '20-20000-00002',
            'customer_price' => 999.00,
            'supplier_cost' => 10.00,
            'status' => 'Completed',
        ])->assertStatus(404);

        // 5. Data Creation Tampering Protection: User A sending company_id = Company B's ID has it forced to Company A
        $responseCreate = $this->actingAs($userA)->post('/orders', [
            'company_id' => $companyB->id, // Tampered company ID
            'order_date' => now()->toDateString(),
            'ebay_order_number' => '20-10000-99999',
            'customer_price' => 150.00,
            'ad_fee_charges' => 5.00,
            'amazon_order_number' => '111-9999999-9999999',
            'supplier_cost' => 60.00,
            'status' => 'Completed',
        ]);

        $createdOrder = Order::where('ebay_order_number', '20-10000-99999')->firstOrFail();
        $this->assertEquals($companyA->id, $createdOrder->company_id, 'Backend must force user assigned company_id.');

        // 6. Reports financial calculation isolation
        $reportService = app(ReportService::class);

        auth()->login($userA);
        $statsA = $reportService->getDashboardStats();
        $this->assertEquals(2, $statsA['total_orders']); // orderA and createdOrder
        $this->assertEquals(250.00, $statsA['customer_revenue']);

        auth()->login($userB);
        $statsB = $reportService->getDashboardStats();
        $this->assertEquals(1, $statsB['total_orders']); // only orderB
        $this->assertEquals(200.00, $statsB['customer_revenue']);
    }
}
