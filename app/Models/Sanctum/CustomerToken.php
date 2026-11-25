<?php

declare(strict_types=1);

namespace App\Models\Sanctum;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CustomerToken extends SanctumPersonalAccessToken
{
    protected $table = 'personal_access_tokens';
}
