<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'company_id',
        'company_name',
        'person_name',
        'transaction_date',
        'max_overdue_date',
        'amount',
        'payment_status',
        'transaction_type',
        'contract_start_date',
        'contract_end_date',
        'contract_file',
        'is_active',
        'file_path'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'max_overdue_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'is_active' => 'boolean',
        'amount' => 'decimal:2'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHistory($query)
    {
        return $query->where('is_active', false);
    }
}
