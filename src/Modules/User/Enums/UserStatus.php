<?php

declare(strict_types=1);

namespace App\Modules\User\Enums;

/**
 * User status enum.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Deleted = 'deleted';
}
