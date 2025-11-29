<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'responsible_user_id',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    /**
     * Get the responsible user for this product
     */
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
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
        return $this->hasMany(Feedback::class, 'project_id');
    }

    /**
     * Scope to filter products for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('responsible_user_id', $userId);
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
