<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\HasBinaryUuid;

class Price extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'sku_id', 
        'branch_id', 
        'type',
        'list_price', 
        'final_price', 
        'min_quantity', 
        'priority',
        'valid_from',
        'valid_to'
    ];

    protected $casts = [
        'list_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    // Scope para obtener precios vigentes
    public function scopeActive(Builder $query)
    {
        $now = now();
        return $query->where('valid_from', '<=', $now)
                     ->where(function($q) use ($now) {
                         $q->whereNull('valid_to')
                           ->orWhere('valid_to', '>=', $now);
                     });
    }

    public function sku() { return $this->belongsTo(Sku::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}