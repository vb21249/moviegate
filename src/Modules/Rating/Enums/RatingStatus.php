<?php

declare(strict_types=1);

namespace App\Modules\Rating\Enums;

/**
 * Rating status enum.
 */
enum RatingStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}