<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'account_manager_id',
        'content',
        'file_path'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }
}
