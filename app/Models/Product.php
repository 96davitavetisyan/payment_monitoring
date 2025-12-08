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
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

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
        return $this->hasMany(Feedback::class, 'project_id');
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
}
