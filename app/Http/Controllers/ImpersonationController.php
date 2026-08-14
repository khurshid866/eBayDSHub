<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    public function start(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin() && !Session::has('original_superadmin_id')) {
            abort(403, 'Unauthorized. Super Admin privileges required for portal impersonation.');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'You are already logged into this account.');
        }

        // Store original Super Admin ID if not already impersonating
        if (!Session::has('original_superadmin_id')) {
            Session::put('original_superadmin_id', $currentUser->id);
        }

        Auth::login($user);

        $companyName = $user->company ? $user->company->name : 'Global';

        return redirect()->route('dashboard')->with('success', "Now accessing portal as {$user->name} ({$user->role} - {$companyName}).");
    }

    public function leave()
    {
        if (!Session::has('original_superadmin_id')) {
            return redirect()->route('dashboard');
        }

        $superAdminId = Session::get('original_superadmin_id');
        Session::forget('original_superadmin_id');

        $superAdmin = User::withoutGlobalScope('company')->find($superAdminId);

        if ($superAdmin) {
            Auth::login($superAdmin);
            return redirect()->route('users.index')->with('success', 'Returned to Super Admin account.');
        }

        return redirect()->route('login');
    }
}
