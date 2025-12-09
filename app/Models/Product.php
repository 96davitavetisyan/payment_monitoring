<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory,SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'start_date',
        'status',
        'own_company_id',
        'type'
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    /**
     * Get the own company that owns this product
     */
    public function ownCompany()
    {
        return $this->belongsTo(OwnCompany::class, 'own_company_id');
    }

    /**
     * Get the contracts for this product
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the feedbacks for this product
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Scope to filter active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter suspended products
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope to filter local products
     */
    public function scopeLocal($query)
    {
        return $query->where('type', 'local');
    }

    /**
     * Scope to filter international products
     */
    public function scopeInternational($query)
    {
        return $query->where('type', 'international');
    }
}
