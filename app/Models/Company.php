<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'logo',
        'status',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'company_id');
    }

    public function importBatches()
    {
        return $this->hasMany(ImportBatch::class, 'company_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'company_id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'company_id');
    }
}
