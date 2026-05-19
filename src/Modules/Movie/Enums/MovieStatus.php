<?php

declare(strict_types=1);

namespace App\Modules\Movie\Enums;

/**
 * Movie status enum.
 */
enum MovieStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
