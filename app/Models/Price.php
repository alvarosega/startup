<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- Importante

class Price extends Model
{
    use SoftDeletes; // <--- Activar el comportamiento

    protected $fillable = [
        'sku_id', 'branch_id', 'list_price', 
        'final_price', 'min_quantity', 'valid_from'
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'valid_from' => 'datetime',
    ];

    public function sku()
    {
        return $this->belongsTo(Sku::class);
    }
}