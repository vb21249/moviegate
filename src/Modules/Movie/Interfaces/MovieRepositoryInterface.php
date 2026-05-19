<?php

declare(strict_types=1);

namespace App\Modules\Movie\Interfaces;

/**
 * Movie repository contract.
 */
interface MovieRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $offset
     * @param string|null $query
     *
     * @return list<array<string, mixed>>
     */
    public function findPublishedMovies(int $limit, int $offset, ?string $query = null): array;

    /**
     * @param string|null $query
     *
     * @return int
     */
    public function countPublishedMovies(?string $query = null): int;

    /**
     * @param int $movieId
     *
     * @return array<string, mixed>|null
     */
    public function findPublishedMovie(int $movieId): ?array;

    /**
     * @param int $movieId
     *
     * @return array<string, int|float|null>
     */
    public function getMovieStats(int $movieId): array;

    /**
     * @param int $movieId
     * @param int $userId
     */
    public function markWatched(int $movieId, int $userId): void;
}
