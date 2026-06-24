<?php

declare(strict_types=1);

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUv7;
use App\Models\Operations\Branch;

class Customer extends Model
{
    use HasUv7, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'branch_id',
        'phone',
        'country_code',
        'email',
        'password',
        'idempotency_key',
        'trust_score',
        'is_active',
        'email_verified_at',
        'latitude',
        'longitude',
        'last_seen_at',
        'last_login_at',
        'deleted_epoch',
        'was_previously_deleted',
        'needs_password_change',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'was_previously_deleted' => 'boolean',
        'needs_password_change' => 'boolean',
        'trust_score' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'email_verified_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deleted_epoch' => 'integer',
    ];

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

    public function billingInfos(): HasMany
    {
        return $this->hasMany(CustomerBillingInfo::class, 'customer_id', 'id');
    }
}