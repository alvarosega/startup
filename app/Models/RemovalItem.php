<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovalItem extends Model
{
    protected $fillable = ['removal_request_id', 'inventory_lot_id', 'quantity', 'unit_cost'];

    public function lot() { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
    // Helper para acceder al SKU a través del lote
    public function sku() { return $this->hasOneThrough(Sku::class, InventoryLot::class, 'id', 'id', 'inventory_lot_id', 'sku_id'); }
}