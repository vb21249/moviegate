<?php

declare(strict_types=1);

namespace App\Modules\Review\Enums;

/**
 * Review status enum.
 */
enum ReviewStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Active = 'active';
    case Archived = 'archived';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases()
        );
    }
}
