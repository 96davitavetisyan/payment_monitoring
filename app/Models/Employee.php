<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'partner_company_id',
        'name',
        'position',
        'email',
        'phone',
    ];

    /**
     * Get the partner company that owns the employee
     */
    public function partnerCompany()
    {
        return $this->belongsTo(PartnerCompany::class);
    }
}
