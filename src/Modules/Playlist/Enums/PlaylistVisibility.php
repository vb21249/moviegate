<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Enums;

/**
 * Playlist visibility values stored in playlists.visibility.
 */
enum PlaylistVisibility: string
{
    case Private = 'private';
    case Public = 'public';
    case Unlisted = 'unlisted';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $visibility): string => $visibility->value,
            self::cases()
        );
    }
}
