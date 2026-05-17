<?php

declare(strict_types=1);

namespace App\Modules\Feed\Enums;

/**
 * Feed status enum.
 */
enum FeedStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}