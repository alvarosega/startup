<?php

declare(strict_types=1);

namespace App\Models\Inventory;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory, SoftDeletes, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'provider_id',
        'admin_id',
        'document_number',
        'purchase_date',
        'payment_type',
        'status',
        'deleted_epoch'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'deleted_epoch' => 'integer'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Branch::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Operations\Provider::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(InventoryLot::class);
    }
}