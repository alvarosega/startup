<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferItem extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transfer_id', 
        'sku_id', 
        'qty_sent', 
        'qty_received'
    ];

    protected $casts = [
        'qty_sent' => 'float',
        'qty_received' => 'float',
    ];

    public function sku(): BelongsTo 
    { 
        return $this->belongsTo(Sku::class); 
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }
}