<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsTo};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUv7;
use App\Models\Operations\Branch;
use App\Enums\Users\DriverStatus;

class Driver extends Authenticatable
{
    use HasUv7, SoftDeletes, Notifiable, HasFactory;

    protected $table = 'drivers';

    protected $fillable = [
        'branch_id', 'phone', 'email', 'password', 'status', 'is_online',
        'is_available', 'last_login_at', 'last_seen_at', 'deleted_epoch',
        'was_previously_deleted', 'needs_password_change',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'status' => DriverStatus::class, // Cast nativo a Backed Enum
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'was_previously_deleted' => 'boolean',
        'needs_password_change' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'deleted_epoch' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Driver $driver) {
            $driver->deleted_epoch = time();
            $driver->saveQuietly();
        });
    }

    // =================================================================================
    // RELACIONES MAPEADAS
    // =================================================================================
    public function profile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'driver_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function billingInfos(): HasMany
    {
        return $this->hasMany(DriverBillingInfo::class, 'driver_id', 'id');
    }

    // =================================================================================
    // LOCAL SCOPES (LÓGICA LOGÍSTICA EN TIEMPO REAL)
    // =================================================================================
    public function scopeByBranch(Builder $query, string $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Filtra conductores listos para recibir pedidos despachados inmediatamente.
     */
    public function scopeAvailableForOrder(Builder $query): Builder
    {
        return $query->where('status', DriverStatus::APPROVED->value)
                     ->where('is_online', true)
                     ->where('is_available', true);
    }
}