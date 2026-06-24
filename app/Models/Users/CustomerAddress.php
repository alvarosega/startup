<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUv7;
use App\Casts\PointCast;
use App\Models\Operations\Branch;

class CustomerAddress extends Model
{
    use HasUv7, SoftDeletes;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'branch_id',
        'alias',
        'address',
        'position', // Campo de persistencia espacial nativa
        'reference',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'position' => PointCast::class, // Abstracción total del binario geospacial
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }
}