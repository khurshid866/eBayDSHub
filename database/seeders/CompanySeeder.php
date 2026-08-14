<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['code' => 'default-enterprise-store'],
            [
                'name' => 'Default Enterprise Store',
                'email' => 'contact@defaultstore.com',
                'phone' => '+1 (555) 019-2834',
                'status' => 'active',
            ]
        );

        Company::updateOrCreate(
            ['code' => 'apex-global-trading'],
            [
                'name' => 'Apex Global Trading',
                'email' => 'sales@apexglobal.com',
                'phone' => '+1 (555) 014-9821',
                'status' => 'active',
            ]
        );
    }
}
