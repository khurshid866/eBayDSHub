<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCompany = Company::where('code', 'default-enterprise-store')->first();
        $companyId = $defaultCompany ? $defaultCompany->id : null;

        $defaults = [
            'currency_symbol' => ['value' => '€', 'type' => 'string', 'description' => 'Global default currency symbol'],
            'default_order_status' => ['value' => 'Completed', 'type' => 'string', 'description' => 'Default status assigned to imported orders'],
            'default_import_mode' => ['value' => 'create', 'type' => 'string', 'description' => 'Default import mode (create/update)'],
            'roi_warning_threshold' => ['value' => '10.0', 'type' => 'float', 'description' => 'ROI percentage threshold for low profit warnings'],
            'pagination_size' => ['value' => '15', 'type' => 'integer', 'description' => 'Default rows per page in data tables'],
            'app_name' => ['value' => 'eBay Dropshipping Hub', 'type' => 'string', 'description' => 'Application display title'],
        ];

        foreach ($defaults as $key => $data) {
            Setting::updateOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                [
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
