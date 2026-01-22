<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id', 
        'sku_id', 
        'qty_sent', 
        'qty_received', 
        'unit_cost'
    ];

    // IMPORTANTE: Para cálculos matemáticos
    protected $casts = [
        'unit_cost' => 'decimal:2',
        'qty_sent' => 'integer',
        'qty_received' => 'integer',
    ];

    // Relación con el Producto
    public function sku() 
    { 
        return $this->belongsTo(Sku::class); 
    }

    // Relación Inversa (Buena práctica)
    public function transfer()
    {
        return $this->belongsTo(Transfer::class);
    }
}