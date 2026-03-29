<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{MorphTo, BelongsTo};
use Illuminate\Support\Str;

class AdCreative extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id', 'campaign_id', 'placement_id', 'brand_id', 'category_id', 'bundle_id',
        'target_type', 'target_id', 'name', 
        'image_mobile_path', 'image_desktop_path',
        'action_type', 'sort_order', 'is_active'
    ];

    public function scopeActive($query) { return $query->where('is_active', true); }

    // --- ANCLAJES (Contexto) ---
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function campaign(): BelongsTo { return $this->belongsTo(AdCampaign::class); }
    public function placement(): BelongsTo { return $this->belongsTo(AdPlacement::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function brand(): BelongsTo { return $this->belongsTo(Brand::class); } // Adición obligatoria
    public function bundle(): BelongsTo { return $this->belongsTo(Bundle::class); }

    /**
     * CORRECCIÓN: El nivel de acceso DEBE ser public.
     * Generación de UUIDv7 para MariaDB 11.8.
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuid7();
    }

    // --- TARGET (Destino) ---
    public function target(): MorphTo { return $this->morphTo(); }

    /**
     * RELACIONES EXPLÍCITAS: Optimizadas para Eager Loading sin filtros ambiguos.
     */
    public function sku(): BelongsTo {
        return $this->belongsTo(Sku::class, 'target_id');
    }
}