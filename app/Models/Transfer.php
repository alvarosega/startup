<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'code', 'origin_branch_id', 'destination_branch_id', 
        'created_by', 'received_by', 'status', 'notes', 
        'shipped_at', 'received_at'
    ];

    public function items() { return $this->hasMany(TransferItem::class); }
    public function origin() { return $this->belongsTo(Branch::class, 'origin_branch_id'); }
    public function destination() { return $this->belongsTo(Branch::class, 'destination_branch_id'); }
    public function sender() { return $this->belongsTo(User::class, 'created_by'); }
}