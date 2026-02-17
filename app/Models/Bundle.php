<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\HasBinaryUuid;

class Bundle extends Model
{
    use SoftDeletes, HasBinaryUuid; // <--- AHORA SÍ LO ENCONTRARÁ

    protected $hidden = ['id', 'branch_id']; // <--- CRÍTICO PARA EVITAR UTF-8 ERROR

    public function toArray()
    {
        $array = parent::toArray();
        $rawId = $this->getRawOriginal('id');
        $array['id'] = $rawId ? bin2hex($rawId) : null;
        
        $rawBranch = $this->getRawOriginal('branch_id');
        $array['branch_id'] = $rawBranch ? bin2hex($rawBranch) : null;
        
        return $array;
    }
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

    public function scopeForBranch(Builder $query, $branchId): void // Quitar el 'int'
    {
        // Aseguramos que la consulta use el binario
        $binaryId = (is_string($branchId) && strlen($branchId) === 32) ? hex2bin($branchId) : $branchId;
        $query->where('branch_id', $binaryId);
    }
}