<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    // Al ser una tabla de snapshot, desactivamos el incremento
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 'sku_id', 'total_physical', 'total_reserved', 'total_safety'
    ];

    // Relaciones si fueran necesarias
    public function sku() { return $this->belongsTo(Sku::class); }
}