<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\{MorphTo, BelongsTo};

class AdCreative extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'branch_id', 'campaign_id', 'placement_id', 'category_id',
        'target_type', 'target_id', 'name', 
        'image_mobile_path', 'image_desktop_path',
        'action_type', 'sort_order', 'is_active'
    ];

    // SCOPE VITAL: Evita errores en seeders
    public function scopeActive($query) { return $query->where('is_active', true); }

    // --- ANCLAJES (Contexto) ---
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function campaign(): BelongsTo { return $this->belongsTo(AdCampaign::class); }
    public function placement(): BelongsTo { return $this->belongsTo(AdPlacement::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }

    // --- TARGET (Destino) ---
    public function target(): MorphTo { return $this->morphTo(); }

    /**
     * CIRUGÍA: Relaciones explícitas para Eager Loading
     * Quitamos el ->where() que causaba el error SQL 1054
     */
    public function sku(): BelongsTo {
        return $this->belongsTo(Sku::class, 'target_id');
    }

    public function bundle(): BelongsTo {
        return $this->belongsTo(Bundle::class, 'target_id');
    }
}