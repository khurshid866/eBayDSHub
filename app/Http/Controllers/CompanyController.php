<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAdminWelcomeMail;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    protected AuditLogService $auditLogger;

    public function __construct(AuditLogService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $activeTab = $request->input('tab', 'active');

        $companies = Company::withCount(['users', 'orders'])->with(['users' => function($q) {
            $q->where('role', 'CompanyAdmin');
        }])->orderBy('name')->paginate(15, ['*'], 'active_page');

        $deletedCompanies = Company::onlyTrashed()->withCount(['users', 'orders'])->with(['users' => function($q) {
            $q->withTrashed()->where('role', 'CompanyAdmin');
        }])->orderByDesc('deleted_at')->paginate(15, ['*'], 'deleted_page');

        return view('companies.index', compact('companies', 'deletedCompanies', 'activeTab'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:companies,code'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = Str::slug($validated['name']);
        }

        $company = Company::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
        ]);

        // Auto-generate a secure random 10-character password for Company Admin
        $plainPassword = Str::random(10);

        $adminUser = User::create([
            'company_id' => $company->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => Hash::make($plainPassword),
            'plain_password' => $plainPassword,
            'assigned_password' => Crypt::encryptString($plainPassword),
            'role' => 'CompanyAdmin',
            'status' => 'active',
        ]);

        // Send email with credentials
        $mailSent = true;
        try {
            Mail::to($adminUser->email)->send(new CompanyAdminWelcomeMail($company, $adminUser, $plainPassword));
        } catch (\Exception $e) {
            $mailSent = false;
            \Illuminate\Support\Facades\Log::error("Failed to send welcome email to company admin {$adminUser->email}: " . $e->getMessage());
        }

        $this->auditLogger->log('created_company', Company::class, $company->id, null, $company->toArray());

        $msg = $mailSent
            ? "Company '{$company->name}' and Company Admin '{$adminUser->name}' registered successfully. Welcome email sent to {$adminUser->email}."
            : "Company '{$company->name}' registered successfully. Could not send email automatically (Mailer log error). You can resend credentials anytime.";

        return redirect()->route('companies.index')->with('success', $msg);
    }

    public function edit(Company $company)
    {
        $this->authorizeSuperAdmin();
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('companies')->ignore($company->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $oldValues = $company->toArray();
        $company->update($validated);

        $this->auditLogger->log('updated_company', Company::class, $company->id, $oldValues, $company->toArray());

        return redirect()->route('companies.index')->with('success', "Company '{$company->name}' updated successfully.");
    }

    public function toggleStatus(Company $company)
    {
        $this->authorizeSuperAdmin();

        $oldStatus = $company->status;
        $newStatus = $company->status === 'active' ? 'inactive' : 'active';

        $company->update(['status' => $newStatus]);

        $this->auditLogger->log('toggled_company_status', Company::class, $company->id, ['status' => $oldStatus], ['status' => $newStatus]);

        return redirect()->back()->with('success', "Company '{$company->name}' status updated to " . strtoupper($newStatus) . ".");
    }

    public function destroy(Company $company)
    {
        $this->authorizeSuperAdmin();

        $name = $company->name;
        $company->delete();

        $this->auditLogger->log('soft_deleted_company', Company::class, $company->id);

        return redirect()->route('companies.index', ['tab' => 'archived'])->with('success', "Company '{$name}' archived / soft deleted successfully.");
    }

    public function restore($id)
    {
        $this->authorizeSuperAdmin();

        $company = Company::onlyTrashed()->findOrFail($id);
        $company->restore();

        $this->auditLogger->log('restored_company', Company::class, $company->id);

        return redirect()->route('companies.index', ['tab' => 'active'])->with('success', "Company '{$company->name}' restored successfully.");
    }

    public function resendCredentials(Company $company)
    {
        $this->authorizeSuperAdmin();

        $admin = $company->users()->where('role', 'CompanyAdmin')->first();

        if (!$admin) {
            return redirect()->route('companies.index')->with('error', "No Company Admin user found for '{$company->name}'.");
        }

        $plainPassword = $admin->getPlainPassword();

        try {
            Mail::to($admin->email)->send(new CompanyAdminWelcomeMail($company, $admin, $plainPassword));
            return redirect()->route('companies.index')->with('success', "Access credentials email successfully sent to Company Admin '{$admin->email}'.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to resend credentials email to {$admin->email}: " . $e->getMessage());
            return redirect()->route('companies.index')->with('error', "Failed to send email to {$admin->email}: " . $e->getMessage());
        }
    }

    public function resetAdminPassword(Request $request, Company $company)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = $company->users()->where('role', 'CompanyAdmin')->first();

        if (!$admin) {
            return redirect()->route('companies.index')->with('error', "No Company Admin user found for '{$company->name}'.");
        }

        $plainPassword = $validated['password'];

        $admin->update([
            'password' => Hash::make($plainPassword),
            'plain_password' => $plainPassword,
            'assigned_password' => Crypt::encryptString($plainPassword),
        ]);

        $this->auditLogger->log('reset_company_admin_password', Company::class, $company->id);

        return redirect()->route('companies.index')->with('success', "Password for Admin '{$admin->name}' ({$company->name}) reset successfully.");
    }

    public function switchCompany(Request $request)
    {
        $this->authorizeSuperAdmin();

        $companyId = $request->input('company_id');

        if (empty($companyId) || $companyId === 'all') {
            Session::forget('active_company_id');
        } else {
            $company = Company::findOrFail($companyId);
            Session::put('active_company_id', $company->id);
        }

        return redirect()->back()->with('success', 'Active company view context updated.');
    }

    protected function authorizeSuperAdmin(): void
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized. Super Admin access required.');
        }
    }
}
