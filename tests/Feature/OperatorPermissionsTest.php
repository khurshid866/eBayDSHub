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
}
