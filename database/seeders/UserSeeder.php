<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCompany = Company::where('code', 'default-enterprise-store')->first();
        $defaultCompanyId = $defaultCompany ? $defaultCompany->id : null;

        // 1. Super Admin (System Wide Access)
        User::updateOrCreate(
            ['email' => 'superadmin@ebay.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'assigned_password' => Crypt::encryptString('password'),
                'role' => 'SuperAdmin',
                'company_id' => null,
                'status' => 'active',
            ]
        );

        // 2. Company Admin (Scoped to Default Enterprise Store)
        User::updateOrCreate(
            ['email' => 'admin@ebay.com'],
            [
                'name' => 'Default Store Admin',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'assigned_password' => Crypt::encryptString('password'),
                'role' => 'CompanyAdmin',
                'company_id' => $defaultCompanyId,
                'status' => 'active',
            ]
        );

        // 3. Operator (Scoped to Default Enterprise Store)
        User::updateOrCreate(
            ['email' => 'operator@ebay.com'],
            [
                'name' => 'Default Store Operator',
                'password' => Hash::make('password'),
                'plain_password' => 'password',
                'assigned_password' => Crypt::encryptString('password'),
                'role' => 'Operator',
                'company_id' => $defaultCompanyId,
                'status' => 'active',
            ]
        );
    }
}
