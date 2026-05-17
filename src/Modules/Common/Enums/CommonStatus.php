<?php

declare(strict_types=1);

namespace App\Modules\Common\Enums;

/**
 * Common status enum.
 */
enum CommonStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}