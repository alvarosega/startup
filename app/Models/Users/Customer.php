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

/**
 * Class Customer
 * * @property string $id
 * @property string|null $branch_id
 * @property string $phone
 * @property string $country_code
 * @property string $email
 * @property string|null $password
 * @property string|null $idempotency_key
 * @property int $trust_score
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed|null $last_known_location
 * @property \Illuminate\Support\Carbon|null $last_seen_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property int $deleted_epoch
 * @property bool $was_previously_deleted
 * @property bool $needs_password_change
 * * @property-read \App\Models\Users\CustomerProfile|null $profile
 * @property-read \App\Models\Operations\Branch|null $branch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Users\CustomerAddress> $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Users\CustomerSocial> $socials
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Users\CustomerBillingInfo> $billingInfos
 */
class Customer extends Authenticatable
{
    use HasUv7, HasRoles, Notifiable, HasFactory;

    protected $table = 'customers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id', 'branch_id', 'phone', 'country_code', 'email', 'password', 'idempotency_key',
        'trust_score', 'is_active', 'email_verified_at', 'last_known_location',
        'last_seen_at', 'last_login_at', 'deleted_epoch', 'was_previously_deleted',
        'needs_password_change',
    ];

    /**
     * BLINDAJE DE SERIALIZACIÓN: Oculta la contraseña y el campo geométrico binario.
     */
    protected $hidden = [
        'password',
        'last_known_location',
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

    /**
     * BLINDAJE DE INTEGRIDAD: Protege el modelo contra mutaciones ilegales y eliminaciones directas.
     */
    protected static function booted(): void
    {
        static::updating(function (Customer $customer) {
            if ($customer->isDirty('id')) {
                throw new \DomainException('Violación de Integridad: No está permitido modificar el identificador UUID del cliente.');
            }
        });

        static::deleting(function (Customer $customer) {
            throw new \DomainException('Operación Prohibida: No se permite la eliminación física de registros. Utilice el Action atómico de borrado lógico.');
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

    // =================================================================================
    // RELACIONES MAPEADAS
    // =================================================================================
    
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

    // =================================================================================
    // LOCAL SCOPES
    // =================================================================================
    
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('deleted_epoch', 0);
    }

    public function scopeByBranch(Builder $query, string $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }
}