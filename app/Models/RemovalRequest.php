<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovalRequest extends Model
{
    protected $fillable = [
        'code', 'branch_id', 'user_id', 
        'status', 'approved_by', 'approved_at', 
        'reason', 'notes'
    ];

    public function items() { return $this->hasMany(RemovalItem::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function requester() { return $this->belongsTo(User::class, 'user_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}