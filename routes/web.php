<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Application Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Orders Management
    Route::post('/orders/bulk-action', [OrderController::class, 'bulkAction'])->name('orders.bulk');
    Route::resource('orders', OrderController::class);

    // Excel Import
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::get('/import/preview', fn () => redirect()->route('import.index')->with('info', 'Please select and upload an Excel spreadsheet file to preview.'));
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::get('/import/confirm', fn () => redirect()->route('import.index')->with('info', 'Import session expired or direct access not allowed. Please upload your spreadsheet file.'));
    Route::post('/import/confirm', [ImportController::class, 'confirmImport'])->name('import.confirm');
    Route::get('/import/history', [ImportController::class, 'history'])->name('import.history');
    Route::get('/import/template', [ImportController::class, 'downloadTemplate'])->name('import.template');
    Route::get('/import/errors/{batch}', [ImportController::class, 'downloadErrors'])->name('import.errors');

    // Reports & Exports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    Route::get('/reports/csv', [ReportController::class, 'exportCsv'])->name('reports.csv');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit_logs.index');

    // Impersonation / Access Portal As User
    Route::post('/impersonate/leave', [ImpersonationController::class, 'leave'])->name('impersonate.leave');
    Route::post('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');

    // User Management
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset_password');
    Route::resource('users', UserController::class);

    // Super Admin Company Scoping & Management
    Route::post('/companies/switch', [CompanyController::class, 'switchCompany'])->name('companies.switch');
    Route::post('/companies/{company}/reset-admin-password', [CompanyController::class, 'resetAdminPassword'])->name('companies.reset_admin_password');
    Route::resource('companies', CompanyController::class);

    // Settings Management
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
