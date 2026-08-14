<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAdminWelcomeMail;
use App\Mail\OperatorWelcomeMail;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected AuditLogService $auditLogger;

    public function __construct(AuditLogService $auditLogger)
    {
        $this->auditLogger = $auditLogger;
    }

    public function index()
    {
        $currentUser = Auth::user();

        if ($currentUser->isSuperAdmin()) {
            $users = User::with('company')->orderBy('name')->paginate(15);
        } else {
            $users = User::where('company_id', $currentUser->company_id)->orderBy('name')->paginate(15);
        }

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $currentUser = Auth::user();
        $companies = $currentUser->isSuperAdmin() ? Company::orderBy('name')->get() : collect();

        return view('users.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            $request->merge(['company_id' => $currentUser->company_id]);
        }

        $roleRules = $currentUser->isSuperAdmin()
            ? ['required', 'string', 'in:SuperAdmin,CompanyAdmin,Operator']
            : ['required', 'string', 'in:Operator'];

        $validated = $request->validate([
            'company_id' => [$currentUser->isSuperAdmin() ? 'nullable' : 'required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => $roleRules,
            'status' => ['required', 'string', 'in:active,inactive'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);
        $validated['plain_password'] = $plainPassword;
        $validated['assigned_password'] = Crypt::encryptString($plainPassword);

        if ($validated['role'] === 'Operator') {
            $validated['permissions'] = $request->input('permissions', []);
        } else {
            $validated['permissions'] = null;
        }

        $user = User::create($validated);

        // Send Welcome Credentials Email
        try {
            if ($user->role === 'Operator') {
                Mail::to($user->email)->send(new OperatorWelcomeMail($user, $plainPassword));
            } elseif ($user->role === 'CompanyAdmin' && $user->company) {
                Mail::to($user->email)->send(new CompanyAdminWelcomeMail($user->company, $user, $plainPassword));
            }
        } catch (\Exception $e) {
            // Safe fallback if mailer is unconfigured
        }

        $this->auditLogger->log('created_user', User::class, $user->id, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'company_id' => $user->company_id,
            'status' => $user->status,
            'permissions' => $user->permissions,
        ]);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully. Access credentials email sent to {$user->email}.");
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized access to user from another company.');
        }

        $companies = $currentUser->isSuperAdmin() ? Company::orderBy('name')->get() : collect();

        return view('users.edit', compact('user', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            $request->merge(['company_id' => $currentUser->company_id]);
        }

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized access to user from another company.');
        }

        // A Company Admin user's role cannot be changed to Operator by a non-superadmin
        if ($user->isCompanyAdmin() && !$currentUser->isSuperAdmin()) {
            $request->merge(['role' => 'CompanyAdmin']);
        }

        $roleRules = $currentUser->isSuperAdmin()
            ? ['required', 'string', 'in:SuperAdmin,CompanyAdmin,Operator']
            : ($user->isCompanyAdmin() ? ['required', 'string', 'in:CompanyAdmin'] : ['required', 'string', 'in:CompanyAdmin,Operator']);

        $validated = $request->validate([
            'company_id' => [$currentUser->isSuperAdmin() ? 'nullable' : 'required', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => $roleRules,
            'status' => ['required', 'string', 'in:active,inactive'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'status', 'company_id', 'permissions']);

        if (!empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($plainPassword);
            $validated['plain_password'] = $plainPassword;
            $validated['assigned_password'] = Crypt::encryptString($plainPassword);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] === 'Operator') {
            $validated['permissions'] = $request->input('permissions', []);
        } else {
            $validated['permissions'] = null;
        }

        $user->update($validated);

        $this->auditLogger->log('updated_user', User::class, $user->id, $oldValues, $user->only(['name', 'email', 'role', 'status', 'company_id', 'permissions']));

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized access to user from another company.');
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $plainPassword = $validated['password'];

        $user->update([
            'password' => Hash::make($plainPassword),
            'plain_password' => $plainPassword,
            'assigned_password' => Crypt::encryptString($plainPassword),
        ]);

        $this->auditLogger->log('reset_user_password', User::class, $user->id);

        return redirect()->route('users.index')->with('success', "Password for '{$user->name}' reset successfully.");
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && $user->company_id !== $currentUser->company_id) {
            abort(403, 'Unauthorized access to user from another company.');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        
        // Soft delete user so associated orders/audit logs remain completely safe
        $user->delete();

        $this->auditLogger->log('soft_deleted_user', User::class, $user->id);

        return redirect()->route('users.index')->with('success', "User '{$name}' soft deleted successfully. All entry history remains preserved.");
    }
}
