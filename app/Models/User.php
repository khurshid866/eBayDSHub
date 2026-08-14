<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'plain_password',
        'assigned_password',
        'role',
        'permissions',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    public static function allPermissions(): array
    {
        return [
            'nav_dashboard' => [
                'name' => 'Dashboard Navigation',
                'category' => 'Navigation Links',
                'description' => 'Access executive dashboard and profit metrics',
            ],
            'nav_orders' => [
                'name' => 'Orders Navigation',
                'category' => 'Navigation Links',
                'description' => 'View orders list table and search entries',
            ],
            'nav_import' => [
                'name' => 'Import Excel Navigation',
                'category' => 'Navigation Links',
                'description' => 'Access Excel file upload and batch import portal',
            ],
            'nav_reports' => [
                'name' => 'Reports Navigation',
                'category' => 'Navigation Links',
                'description' => 'View profit reports and financial summaries',
            ],
            'nav_audit_logs' => [
                'name' => 'Audit Logs Navigation',
                'category' => 'Navigation Links',
                'description' => 'View system audit trail and user activity history',
            ],
            'action_create_order' => [
                'name' => 'Create New Order Action',
                'category' => 'Order Actions',
                'description' => 'Permission to enter and create new dropshipping orders',
            ],
            'action_edit_order' => [
                'name' => 'Edit Order Action',
                'category' => 'Order Actions',
                'description' => 'Permission to modify existing order details and prices',
            ],
            'action_delete_order' => [
                'name' => 'Delete Order Action',
                'category' => 'Order Actions',
                'description' => 'Permission to soft-delete order records',
            ],
            'action_bulk_orders' => [
                'name' => 'Bulk Order Operations Action',
                'category' => 'Order Actions',
                'description' => 'Permission to perform bulk status updates and bulk deletion',
            ],
            'action_export_reports' => [
                'name' => 'Export Reports Action',
                'category' => 'Report Actions',
                'description' => 'Permission to download PDF, Excel, and CSV financial exports',
            ],
        ];
    }

    public function hasPermission(string $permissionKey): bool
    {
        if ($this->isSuperAdmin() || $this->isCompanyAdmin()) {
            return true;
        }

        if ($this->permissions === null) {
            return true; // Default full access if permissions matrix is unconfigured
        }

        return in_array($permissionKey, $this->permissions);
    }

    public function getPlainPassword(): string
    {
        if (!empty($this->plain_password)) {
            return $this->plain_password;
        }

        if (!empty($this->assigned_password)) {
            try {
                return Crypt::decryptString($this->assigned_password);
            } catch (\Exception $e) {
                return $this->assigned_password;
            }
        }

        return 'password';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'SuperAdmin' || $this->role === 'CompanyAdmin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'SuperAdmin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'CompanyAdmin';
    }

    public function isOperator(): bool
    {
        return $this->role === 'Operator';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function updatedOrders()
    {
        return $this->hasMany(Order::class, 'updated_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
