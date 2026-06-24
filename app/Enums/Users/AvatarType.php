<?php

declare(strict_types=1);

namespace App\Enums\Users;

enum AvatarType: string
{
    case ICON = 'icon';
    case CUSTOM_IMAGE = 'custom_image';
}