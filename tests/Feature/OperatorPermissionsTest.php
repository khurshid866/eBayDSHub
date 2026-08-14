<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OperatorPermissionsTest extends TestCase
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

    public function test_operator_with_restricted_permissions_is_denied(): void
    {
        $company = Company::create(['name' => 'Perm Test Company', 'code' => 'perm-comp-' . uniqid()]);

        $operator = User::create([
            'company_id' => $company->id,
            'name' => 'Restricted Operator',
            'email' => 'restricted_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'Operator',
            'permissions' => ['nav_dashboard'], // Only allowed dashboard
            'status' => 'active',
        ]);

        // Allowed route
        $this->actingAs($operator)->get('/dashboard')->assertStatus(200);

        // Forbidden routes (should return 403)
        $this->actingAs($operator)->get('/import')->assertStatus(403);
        $this->actingAs($operator)->get('/reports')->assertStatus(403);
        $this->actingAs($operator)->get('/audit-logs')->assertStatus(403);
    }

    public function test_company_admin_role_cannot_be_demoted_to_operator(): void
    {
        $company = Company::create(['name' => 'Role Protection Co', 'code' => 'role-co-' . uniqid()]);

        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Store Admin',
            'email' => 'storeadmin_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->put("/users/{$admin->id}", [
            'company_id' => $company->id,
            'name' => 'Store Admin Renamed',
            'email' => $admin->email,
            'role' => 'Operator', // Attempting demotion to Operator
            'status' => 'active',
        ]);

        $admin->refresh();
        $this->assertEquals('CompanyAdmin', $admin->role);
        $this->assertEquals('Store Admin Renamed', $admin->name);
    }

    public function test_resend_company_admin_and_operator_credentials_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $company = Company::create(['name' => 'Mail Test Co', 'code' => 'mail-co-' . uniqid()]);

        $superAdmin = User::create([
            'name' => 'Super Admin Mail',
            'email' => 'supermail_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
            'status' => 'active',
        ]);

        $admin = User::create([
            'company_id' => $company->id,
            'name' => 'Admin Mail Test',
            'email' => 'adminmail_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'plain_password' => 'secret123',
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        $responseCompany = $this->actingAs($superAdmin)->post("/companies/{$company->id}/resend-credentials");
        $responseCompany->assertRedirect('/companies');

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\CompanyAdminWelcomeMail::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email) && str_contains($mail->loginUrl, 'https://ebay.luxconvo.com');
        });

        $responseUser = $this->actingAs($superAdmin)->post("/users/{$admin->id}/resend-credentials");
        $responseUser->assertRedirect('/users');
    }

    public function test_super_admin_can_soft_delete_restore_and_toggle_company_status(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin Actions',
            'email' => 'superactions_' . uniqid() . '@ebay.com',
            'password' => bcrypt('password'),
            'role' => 'SuperAdmin',
            'status' => 'active',
        ]);

        $company = Company::create([
            'name' => 'Action Co',
            'code' => 'act-co-' . uniqid(),
            'status' => 'active',
        ]);

        // 1. Toggle status from active to inactive
        $this->actingAs($superAdmin)->post("/companies/{$company->id}/toggle-status");
        $company->refresh();
        $this->assertEquals('inactive', $company->status);

        // 2. Toggle status back to active
        $this->actingAs($superAdmin)->post("/companies/{$company->id}/toggle-status");
        $company->refresh();
        $this->assertEquals('active', $company->status);

        // 3. Soft Delete / Archive company
        $responseDel = $this->actingAs($superAdmin)->delete("/companies/{$company->id}");
        $responseDel->assertRedirect('/companies?tab=archived');
        $this->assertSoftDeleted('companies', ['id' => $company->id]);

        // 4. Restore company
        $responseRestore = $this->actingAs($superAdmin)->post("/companies/{$company->id}/restore");
        $responseRestore->assertRedirect('/companies?tab=active');
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }
}
