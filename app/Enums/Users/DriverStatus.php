<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum DriverStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';
}