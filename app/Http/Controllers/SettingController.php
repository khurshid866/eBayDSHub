<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    protected AuditLogService $auditLogger;

    public function __construct(AuditLogService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index()
    {
        $this->authorize('viewAny', Setting::class);

        $user = Auth::user();
        $company = $user->company;

        $settings = [
            'currency_symbol' => Setting::get('currency_symbol', '€'),
            'default_order_status' => Setting::get('default_order_status', 'Completed'),
            'default_import_mode' => Setting::get('default_import_mode', 'create'),
            'roi_warning_threshold' => Setting::get('roi_warning_threshold', 10.0),
            'pagination_size' => Setting::get('pagination_size', 15),
            'app_name' => Setting::get('app_name', 'eBay Dropshipping Hub'),
        ];

        return view('settings.index', compact('settings', 'company'));
    }

    public function update(Request $request)
    {
        $this->authorize('update', Setting::class);

        $user = Auth::user();
        $company = $user->company;

        $validated = $request->validate([
            'currency_symbol' => ['required', 'string', 'max:10'],
            'default_order_status' => ['required', 'string', 'in:Pending,Purchased,Shipped,Delivered,Completed,Cancelled,Refunded'],
            'default_import_mode' => ['required', 'string', 'in:create,update'],
            'roi_warning_threshold' => ['required', 'numeric', 'min:0', 'max:100'],
            'pagination_size' => ['required', 'integer', 'min:5', 'max:100'],
            'app_name' => ['required', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        // Handle Company Logo upload
        if ($company && $request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = 'company_' . $company->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('logos');

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $logoUrl = 'logos/' . $filename;

            $company->update(['logo' => $logoUrl]);
        }

        if ($company && !empty($validated['company_name'])) {
            $company->update(['name' => $validated['company_name']]);
        }

        $oldValues = [
            'currency_symbol' => Setting::get('currency_symbol'),
            'default_order_status' => Setting::get('default_order_status'),
            'default_import_mode' => Setting::get('default_import_mode'),
            'roi_warning_threshold' => Setting::get('roi_warning_threshold'),
            'pagination_size' => Setting::get('pagination_size'),
            'app_name' => Setting::get('app_name'),
        ];

        $settingKeys = ['currency_symbol', 'default_order_status', 'default_import_mode', 'roi_warning_threshold', 'pagination_size', 'app_name'];

        foreach ($settingKeys as $key) {
            if (isset($validated[$key])) {
                Setting::set($key, $validated[$key]);
            }
        }

        $this->auditLogger->log('updated_settings', Setting::class, null, $oldValues, $validated);

        return redirect()->route('settings.index')->with('success', 'Company settings & branding updated successfully.');
    }
}
