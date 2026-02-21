<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Brand extends Model
{
    use SoftDeletes, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'provider_id', 'name', 'slug', 'image_path', 
        'website', 'is_active', 'is_featured', 'sort_order', 'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relación con el silo de proveedores
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    // Scope para evitar BadMethodCallException en controladores
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}