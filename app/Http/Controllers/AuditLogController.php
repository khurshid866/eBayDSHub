<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()?->hasPermission('nav_audit_logs')) {
            abort(403, 'Access denied. You do not have permission to access Audit Logs.');
        }

        $query = AuditLog::with('user')->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%' . $request->input('action') . '%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('audit_logs.index', compact('logs'));
    }
}
