<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Enums;

/**
 * Playlist status enum.
 */
enum PlaylistStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}