<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    protected $fillable = [
        'branch_id', 'sku_id', 'inventory_lot_id', 'user_id',
        'type', 'quantity', 'unit_cost', 'reference'
    ];
}