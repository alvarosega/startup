<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Sku;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Favorite extends Model
{
    protected $fillable = [
        'customer_id',
        'sku_id',
    ];

    /**
     * Nodo de pertenencia del SKU favorito.
     */
    public function sku(): BelongsTo
    {
        return $this->belongsTo(Sku::class);
    }

    /**
     * Referencia al cliente propietario del registro.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User\Customer::class, 'customer_id');
    }
}