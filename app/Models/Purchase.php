<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'provider_id', 'user_id', 
        'document_number', 'purchase_date', 
        'total_amount', 'notes', 'status'
    ];

    public function provider() { return $this->belongsTo(Provider::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function user() { return $this->belongsTo(User::class); }
    
    // Relación con los Lotes (Detalles)
    public function inventoryLots() { return $this->hasMany(InventoryLot::class); }
}