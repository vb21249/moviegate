<?php

declare(strict_types=1);

namespace App\Modules\Rating\Interfaces;

/**
 * Rating repository contract.
 */
interface RatingRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findRatings(
        int $limit,
        int $offset,
        ?int $movieId = null,
        ?int $userId = null,
        ?int $score = null
    ): array;

    public function countRatings(?int $movieId = null, ?int $userId = null, ?int $score = null): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findRating(int $ratingId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findOwnedRating(int $ratingId, int $userId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findOwnedRatingForMovie(int $userId, int $movieId): ?array;

    public function movieExists(int $movieId): bool;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function createRating(int $userId, array $attributes): array;

    /**
     * @param array<string, mixed> $attributes
     */
    public function updateRating(int $ratingId, int $userId, array $attributes): void;

    public function softDeleteRating(int $ratingId, int $userId): void;

    /**
     * @return list<array<string, mixed>>
     */
    public function findUserHistory(int $userId, int $limit, int $offset, ?int $movieId = null): array;

    public function countUserHistory(int $userId, ?int $movieId = null): int;
}
