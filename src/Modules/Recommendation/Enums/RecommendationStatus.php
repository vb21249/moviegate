<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Enums;

/**
 * Recommendation status enum.
 */
enum RecommendationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}