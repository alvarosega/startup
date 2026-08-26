<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasUv7;
use App\Models\Operations\Branch;
use App\Exceptions\Customer\Auth\ImmutableUuidException;
use App\Exceptions\Customer\Auth\PhysicalDeletionProhibitedException;


class CustomerAddress extends Model
{
    use HasUv7;

    protected $table = 'customer_addresses';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'customer_id',
        'branch_id',
        'alias',
        'address',
        'position',
        'reference',
        'is_default',
        'deleted_epoch',
    ];

    /**
     * BLINDAJE DE SERIALIZACIÓN: Oculta el campo geométrico binario crudo
     * para mitigar colapsos de codificación UTF-8 en llamadas de tipo array/json.
     */
    protected $hidden = [
        'position',
    ];

    protected $casts = [
        'is_default'    => 'boolean',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (CustomerAddress $address) {
            if ($address->isDirty('id') || $address->isDirty('customer_id')) {
                throw new ImmutableUuidException($address->isDirty('id') ? 'id' : 'customer_id');
            }
        });

        static::deleting(function (CustomerAddress $address) {
            throw new PhysicalDeletionProhibitedException('direcciones');
        });
    }
    // =================================================================================
    // RELACIONES MAPEADAS
    // =================================================================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    // =================================================================================
    // LOCAL SCOPES
    // =================================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('deleted_epoch', 0);
    }
}