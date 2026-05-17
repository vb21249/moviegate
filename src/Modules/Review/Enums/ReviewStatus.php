<?php

declare(strict_types=1);

namespace App\Modules\Review\Enums;

/**
 * Review status enum.
 */
enum ReviewStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}