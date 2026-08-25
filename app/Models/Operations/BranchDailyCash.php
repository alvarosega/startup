<?php

declare(strict_types=1);

namespace App\Models\Operations;

use App\Models\Users\Admin;
use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchDailyCash extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'opened_by_admin_id',
        'closed_by_admin_id',
        'date',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'difference',
        'status',
        'closing_notes',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'opening_amount'  => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'actual_amount'   => 'decimal:2',
        'difference'      => 'decimal:2',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'opened_by_admin_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'closed_by_admin_id');
    }
}