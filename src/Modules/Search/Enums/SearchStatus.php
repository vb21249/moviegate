<?php

declare(strict_types=1);

namespace App\Modules\Search\Enums;

/**
 * Search status enum.
 */
enum SearchStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}