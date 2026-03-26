<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BundleItem extends Pivot
{
    /**
     * Al ser una tabla con llave primaria compuesta (bundle_id, sku_id),
     * desactivamos el incremento y el manejo de ID único estándar.
     */
    protected $table = 'bundle_items';
    
    public $incrementing = false;
    
    protected $fillable = [
        'bundle_id',
        'sku_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer'
    ];
}