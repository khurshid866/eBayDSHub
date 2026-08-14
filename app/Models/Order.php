<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'order_date',
        'ebay_order_number',
        'ebay_tracking_number',
        'customer_price',
        'ad_fee_charges',
        'amazon_order_number',
        'supplier_cost',
        'ebay_net',
        'profit',
        'roi',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'customer_price' => 'decimal:2',
            'ad_fee_charges' => 'decimal:2',
            'supplier_cost' => 'decimal:2',
            'ebay_net' => 'decimal:2',
            'profit' => 'decimal:2',
            'roi' => 'float',
        ];
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('ebay_order_number', 'like', "%{$search}%")
                ->orWhere('ebay_tracking_number', 'like', "%{$search}%")
                ->orWhere('amazon_order_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    public function scopeFilterByDate($query, $fromDate = null, $toDate = null)
    {
        if (!empty($fromDate)) {
            $query->whereDate('order_date', '>=', $fromDate);
        }
        if (!empty($toDate)) {
            $query->whereDate('order_date', '<=', $toDate);
        }
        return $query;
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'model_id')->where('model_type', self::class)->orderByDesc('created_at');
    }
}
