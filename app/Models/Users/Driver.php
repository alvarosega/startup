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

class Driver extends Model
{
    use HasUv7, SoftDeletes;

    protected $table = 'drivers';

    protected $fillable = [
        'branch_id',
        'phone',
        'email',
        'password',
        'status',
        'is_online',
        'is_available',
        'last_login_at',
        'last_seen_at',
        'deleted_epoch',
        'was_previously_deleted',
        'needs_password_change',
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'is_available' => 'boolean',
        'was_previously_deleted' => 'boolean',
        'needs_password_change' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'deleted_epoch' => 'integer',
    ];

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
}