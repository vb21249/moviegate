<?php

declare(strict_types=1);

namespace App\Modules\Auth\Enums;

/**
 * Auth status enum.
 */
enum AuthStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}