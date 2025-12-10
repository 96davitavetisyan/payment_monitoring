<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'contract_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'payment_status',
        'paid_date',
        'notes',
        'notified_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'notified_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the contract for this transaction
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get the files for this transaction
     */
    public function files()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Scope for pending payments
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('payment_status', ['late', 'overdue']);
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
