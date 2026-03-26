<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, BelongsTo};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Carbon\Carbon;

class Bundle extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 
        'name', 
        'slug', 
        'type', // Discriminador: 'atomic' | 'template'
        'description', 
        'image_path', 
        'is_active', 
        'fixed_price', 
        'max_quantity_per_order',
        'starts_at', 
        'ends_at'
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'fixed_price' => 'decimal:2',
        'starts_at'   => 'datetime', 
        'ends_at'     => 'datetime',
        'max_quantity_per_order' => 'integer',
    ];

    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps()
                    ->using(BundleItem::class);
    }

    public function branch(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class); 
    }

    // --- SCOPES DE TELEMETRÍA ---

    /**
     * Filtra solo bundles activos y dentro de ventana de tiempo válida.
     */
    public function scopeActive(Builder $query): void
    {
        $now = Carbon::now();
        $query->where('is_active', true)
              ->where(function ($q) use ($now) {
                  $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
              })
              ->where(function ($q) use ($now) {
                  $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
              });
    }

    // --- HELPERS DE LÓGICA ---

    public function isAtomic(): bool
    {
        return $this->type === 'atomic';
    }

    public function isTemplate(): bool
    {
        return $this->type === 'template';
    }
}