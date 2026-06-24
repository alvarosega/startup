<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum VehicleType: string
{
    case MOTORCYCLE = 'motorcycle';
    case BICYCLE = 'bicycle';
    case CAR = 'car';
    case VAN = 'van';
}