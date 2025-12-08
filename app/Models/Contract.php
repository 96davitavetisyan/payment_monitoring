<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'partner_company_id',
        'own_company_id',
        'product_id',
        'contract_number',
        'contract_start_date',
        'contract_end_date',
        'payment_type',
        'payment_amount',
        'status',
        'contract_file',
        'notes',
        'account_number',
        'payment_date',
        'payment_finish_date',

    ];

    protected $casts = [
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    /**
     * Get the partner company for this contract
     */
    public function partnerCompany()
    {
        return $this->belongsTo(PartnerCompany::class);
    }

    /**
     * Get the own company for this contract
     */
    public function ownCompany()
    {
        return $this->belongsTo(OwnCompany::class);
    }

    /**
     * Get the product for this contract
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the transactions/invoices for this contract
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get users assigned to this contract
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'contract_user');
    }
}
