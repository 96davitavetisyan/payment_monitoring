<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name','start_date','responsible_user_id','status'];

    public function responsibleUser() { return $this->belongsTo(User::class,'responsible_user_id'); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function feedbacks() { return $this->hasMany(Feedback::class); }
    public function scopeForUser($query,$userId) { return $query->where('responsible_user_id',$userId); }
}
