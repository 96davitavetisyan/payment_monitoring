<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['project_id','account_manager_id','content'];
    public function project() { return $this->belongsTo(Project::class); }
    public function accountManager() { return $this->belongsTo(User::class,'account_manager_id'); }
}
