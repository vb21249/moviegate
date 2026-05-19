<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Transformers;

/**
 * Builds playlist-specific API payload fragments.
 */
final class PlaylistTransformer
{
    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function movieItem(array $row): array
    {
        return [
            'id' => (int) $row['playlist_movie_id'],
            'position' => (int) $row['position'],
            'added_at' => $row['added_at'] !== null ? (string) $row['added_at'] : null,
            'movie' => [
                'id' => (int) $row['movie_id'],
                'slug' => (string) $row['movie_slug'],
                'title' => (string) $row['movie_title'],
                'poster_url' => $row['movie_poster_url'] !== null ? (string) $row['movie_poster_url'] : null,
                'release_date' => $row['movie_release_date'] !== null ? (string) $row['movie_release_date'] : null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $movieItem
     *
     * @return array<string, mixed>
     */
    public function movieAddedPayload(int $playlistId, array $movieItem): array
    {
        return [
            'message' => 'Movie added to playlist',
            'playlist_id' => $playlistId,
            'item' => $movieItem,
        ];
    }

    /**
     * @return array<string, int|bool|string>
     */
    public function movieRemovedPayload(int $playlistId, int $movieId): array
    {
        return [
            'message' => 'Movie removed from playlist',
            'removed' => true,
            'playlist_id' => $playlistId,
            'movie_id' => $movieId,
        ];
    }
}
