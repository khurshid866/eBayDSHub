<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'original_filename',
        'file_type',
        'total_rows',
        'inserted_rows',
        'updated_rows',
        'skipped_rows',
        'failed_rows',
        'imported_by',
        'started_at',
        'completed_at',
        'status',
        'error_summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_summary' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }
}
