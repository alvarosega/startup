<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 
        'user_id', 
        'source_sku_id', 
        'quantity_removed',
        'destination_sku_id', 
        'quantity_added',
        'notes'
    ];

    // --- RELACIONES FALTANTES ---

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sourceSku()
    {
        return $this->belongsTo(Sku::class, 'source_sku_id');
    }

    public function destinationSku()
    {
        return $this->belongsTo(Sku::class, 'destination_sku_id');
    }
}