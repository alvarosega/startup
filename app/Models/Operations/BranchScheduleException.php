<?php

declare(strict_types=1);

namespace App\Models\Operations;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchScheduleException extends Model
{
    use HasFactory, HasUv7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'date',
        'opening_time',
        'closing_time',
        'is_closed',
        'reason',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_closed' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}