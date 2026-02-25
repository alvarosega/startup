<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, BelongsTo, MorphMany};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Bundle extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $hidden = ['deleted_at']; 

    protected $fillable = [
        'branch_id', 'name', 'slug', 'description', 'image_path', 'is_active', 'fixed_price'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fixed_price' => 'decimal:2',
    ];

    public static function getPaginatedForAdmin(array $filters)
    {
        return self::with(['branch', 'skus'])
            ->withCount(['skus'])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($filters['branch_id'] ?? null, fn($q, $b) => $q->where('branch_id', $b))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function branch(): BelongsTo 
    { 
        return $this->belongsTo(Branch::class); 
    }

    public function skus(): BelongsToMany
    {
        return $this->belongsToMany(Sku::class, 'bundle_items')
                    ->using(BundleItem::class)
                    ->withPivot('quantity') 
                    ->withTimestamps();
    }


    public function scopeForBranch(Builder $query, string $branchId): void
    {
        $query->where('branch_id', $branchId);
    }
}