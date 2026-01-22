<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferItem extends Model
{
    protected $fillable = ['transfer_id', 'sku_id', 'qty_sent', 'qty_received', 'unit_cost'];

    public function sku() { return $this->belongsTo(Sku::class); }
}