<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerCompany extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'tax_id',
        'contact_person',
        'contact_person_position',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the contracts for this partner company
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the employees for this partner company
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
