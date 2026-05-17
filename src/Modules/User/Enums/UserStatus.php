<?php

declare(strict_types=1);

namespace App\Modules\User\Enums;

/**
 * User status enum.
 */
enum UserStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}