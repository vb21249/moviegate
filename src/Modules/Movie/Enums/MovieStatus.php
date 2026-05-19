<?php

declare(strict_types=1);

namespace App\Modules\Movie\Enums;

/**
 * Movie status enum.
 */
enum MovieStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';
}
