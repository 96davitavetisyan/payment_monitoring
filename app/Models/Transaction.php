<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['project_id','customer_name','transaction_date','amount','payment_status','is_active','file_path'];
    public function project() { return $this->belongsTo(Project::class); }
    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeHistory($query) { return $query->where('is_active', false); }
}
