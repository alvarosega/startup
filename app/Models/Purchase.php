<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasUuidv7;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasUuidv7;

    protected $fillable = [
        'branch_id', 'provider_id', 'user_id', 
        'document_number', 'purchase_date', 
        'payment_type', 'payment_due_date', 
        'total_amount', 'notes', 'status'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'payment_due_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function inventoryLots() { return $this->hasMany(InventoryLot::class); }
}