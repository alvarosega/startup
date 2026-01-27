<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder; // Importante para el Scope

class Bundle extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'branch_id', // <--- NUEVO
        'name', 
        'slug', 
        'description', 
        'image_path', 
        'is_active', 
        'fixed_price'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fixed_price' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    // --- SCOPES (Para facilitar consultas) ---

    /**
     * Filtra los bundles por la sucursal activa automáticamente si se usa.
     */
    public function scopeForBranch(Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }
}