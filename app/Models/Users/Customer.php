<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsTo};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasUv7;
use App\Models\Operations\Branch;
use Spatie\Permission\Traits\HasRoles;
use App\Exceptions\Customer\Auth\ImmutableUuidException;
use App\Exceptions\Customer\Auth\PhysicalDeletionProhibitedException;

class Customer extends Authenticatable
{
    use HasUv7, HasRoles, Notifiable, HasFactory;

    protected $table = 'customers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'branch_id', 'phone', 'country_code', 'email', 'password', 'idempotency_key',
        'trust_score', 'is_active', 'email_verified_at',
        'last_seen_at', 'last_login_at', 'deleted_epoch', 'was_previously_deleted',
        'needs_password_change',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'was_previously_deleted' => 'boolean',
        'needs_password_change' => 'boolean',
        'trust_score' => 'integer',
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deleted_epoch' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (Customer $customer) {
            if ($customer->isDirty('id')) {
                throw new ImmutableUuidException('id');
            }
        });

        static::deleting(function (Customer $customer) {
            throw new PhysicalDeletionProhibitedException('clientes');
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query->when(!empty($filters['search']), function (Builder $q) use ($filters) {
            $search = $filters['search'];
            $q->where(function (Builder $inner) use ($search) {
                $inner->where('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhereHas('profile', function (Builder $qp) use ($search) {
                          $qp->where('first_name', 'like', "%{$search}%")
                             ->orWhere('last_name', 'like', "%{$search}%");
                      });
            });
        })->when(isset($filters['is_active']) && $filters['is_active'] !== '', function (Builder $q) use ($filters) {
            $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        })->when(!empty($filters['branch_id']), function (Builder $q) use ($filters) {
            $q->where('branch_id', $filters['branch_id']);
        });
    }

    public function profile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class, 'customer_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id', 'id');
    }

    public function socials(): HasMany
    {
        return $this->hasMany(CustomerSocial::class, 'customer_id', 'id');
    }

    public function billingInfos(): HasMany
    {
        return $this->hasMany(CustomerBillingInfo::class, 'customer_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }

    public function scopeByBranch(Builder $query, string $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }
}