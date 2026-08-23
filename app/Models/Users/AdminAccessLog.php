<?php

declare(strict_types=1);

namespace App\Models\Users;

use App\Traits\HasUv7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAccessLog extends Model
{
    use HasFactory, HasUv7;

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'email_attempted',
        'ip_address',
        'user_agent',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}