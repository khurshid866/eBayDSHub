<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImpersonationTest extends TestCase
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

    public function test_super_admin_can_impersonate_user_and_return(): void
    {
        $company = Company::create(['name' => 'Test Company', 'code' => 'test-comp-' . uniqid()]);

        $superAdmin = User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin_imp_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
            'status' => 'active',
        ]);

        $companyAdmin = User::create([
            'company_id' => $company->id,
            'name' => 'Store Admin Test',
            'email' => 'storeadmin_imp_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        // 1. Super Admin starts impersonating Company Admin
        $response = $this->actingAs($superAdmin)->post("/impersonate/{$companyAdmin->id}");
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($companyAdmin);
        $this->assertEquals($superAdmin->id, session('original_superadmin_id'));

        // 2. Impersonated user leaves impersonation and returns to Super Admin
        $responseLeave = $this->post('/impersonate/leave');
        $responseLeave->assertRedirect('/users');
        $this->assertAuthenticatedAs($superAdmin);
        $this->assertNull(session('original_superadmin_id'));
    }
}
