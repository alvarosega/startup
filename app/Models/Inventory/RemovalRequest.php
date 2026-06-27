<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RemovalRequest extends Model
{
    use HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'branch_id', 'admin_id', 'approved_by_id', 
        'status', 'approved_at', 'reason', 'notes'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Una baja consolidada como approved es estrictamente inmutable.');
        });

        static::deleting(function () {
            throw new \BadMethodCallException('VIOLACIÓN_CONTABLE: Prohibido eliminar registros de mermas del histórico financiero.');
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class, 'admin_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Users\Admin::class, 'approved_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RemovalItem::class);
    }
}