<?php

declare(strict_types=1);

namespace App\Modules\Playlist\Interfaces;

/**
 * Playlist repository contract.
 */
interface PlaylistRepositoryInterface
{
    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string|null $visibility
     * @param string|null $query
     *
     * @return list<array<string, mixed>>
     */
    public function findForUser(
        int $userId,
        int $limit,
        int $offset,
        ?string $visibility = null,
        ?string $query = null
    ): array;

    /**
     * @param int $userId
     * @param string|null $visibility
     * @param string|null $query
     *
     * @return int
     */
    public function countForUser(int $userId, ?string $visibility = null, ?string $query = null): int;

    /**
     * @param int $playlistId
     * @param int $userId
     *
     * @return array<string, mixed>|null
     */
    public function findAccessiblePlaylist(int $playlistId, int $userId): ?array;

    /**
     * @param int $playlistId
     * @param int $userId
     *
     * @return array<string, mixed>|null
     */
    public function findOwnedPlaylist(int $playlistId, int $userId): ?array;

    /**
     * @param int $playlistId
     *
     * @return array<string, int>
     */
    public function getPlaylistStats(int $playlistId): array;

    /**
     * @param int $playlistId
     *
     * @return list<array<string, mixed>>
     */
    public function findPlaylistMovies(int $playlistId): array;

    /**
     * @param int $userId
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function createPlaylist(int $userId, array $attributes): array;

    /**
     * @param int $playlistId
     * @param array<string, mixed> $attributes
     */
    public function updatePlaylist(int $playlistId, array $attributes): void;

    /**
     * @param int $playlistId
     * @param int $userId
     */
    public function softDeletePlaylist(int $playlistId, int $userId): void;

    /**
     * @param int $userId
     * @param string $slug
     * @param int|null $excludePlaylistId
     *
     * @return bool
     */
    public function slugExistsForUser(int $userId, string $slug, ?int $excludePlaylistId = null): bool;

    /**
     * @param int $userId
     * @param int|null $excludePlaylistId
     */
    public function clearDefaultWatchLater(int $userId, ?int $excludePlaylistId = null): void;

    /**
     * @param int $movieId
     *
     * @return bool
     */
    public function movieExists(int $movieId): bool;

    /**
     * @param int $playlistId
     *
     * @return int
     */
    public function nextPosition(int $playlistId): int;

    /**
     * @param int $playlistId
     * @param int $movieId
     * @param int $position
     *
     * @return array<string, mixed>
     */
    public function addMovieToPlaylist(int $playlistId, int $movieId, int $position): array;

    /**
     * @param int $playlistId
     * @param int $movieId
     *
     * @return bool
     */
    public function removeMovieFromPlaylist(int $playlistId, int $movieId): bool;
}
