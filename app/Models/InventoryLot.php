<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // <--- IMPORTAR
class InventoryLot extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'purchase_id', 'transfer_id',
        'sku_id', 'branch_id', 'lot_code',
        'quantity', 'initial_quantity', 'reserved_quantity',
        'unit_cost', 'expiration_date'
    ];

    protected $casts = [
        'expiration_date' => 'date',
        'unit_cost' => 'decimal:2'
    ];

    public function sku() { return $this->belongsTo(Sku::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
    // public function transfer() { return $this->belongsTo(Transfer::class); } // Descomentar cuando tengas Transfer
}