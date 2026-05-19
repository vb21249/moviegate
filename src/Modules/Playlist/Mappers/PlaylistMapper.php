<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Mappers;

use App\Modules\Playlist\DTO\PlaylistDto;

/**
 * Maps persistence rows into playlist DTOs.
 */
final class PlaylistMapper
{
    /**
     * @param array<string, mixed> $row
     * @param array<string, int> $stats
     * @param list<array<string, mixed>> $movies
     *
     * @return PlaylistDto
     */
    public function mapPlaylist(array $row, array $stats = [], array $movies = []): PlaylistDto
    {
        return new PlaylistDto(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            name: (string) $row['name'],
            slug: (string) $row['slug'],
            description: $row['description'] !== null ? (string) $row['description'] : null,
            visibility: (string) $row['visibility'],
            isDefaultWatchLater: (bool) $row['is_default_watch_later'],
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            updatedAt: $row['updated_at'] !== null ? (string) $row['updated_at'] : null,
            stats: $stats,
            movies: $movies,
        );
    }
}
