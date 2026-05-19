<?php

declare(strict_types=1);

namespace App\Modules\Review\Interfaces;

/**
 * Review repository contract.
 */
interface ReviewRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $offset
     * @param string|null $query
     * @param int|null $movieId
     * @param int|null $userId
     *
     * @return list<array<string, mixed>>
     */
    public function findPublishedReviews(
        int $limit,
        int $offset,
        ?string $query = null,
        ?int $movieId = null,
        ?int $userId = null
    ): array;

    /**
     * @param string|null $query
     * @param int|null $movieId
     * @param int|null $userId
     *
     * @return int
     */
    public function countPublishedReviews(?string $query = null, ?int $movieId = null, ?int $userId = null): int;

    /**
     * @param int $reviewId
     *
     * @return array<string, mixed>|null
     */
    public function findPublishedReview(int $reviewId): ?array;

    /**
     * @param int $reviewId
     * @param int $userId
     *
     * @return array<string, mixed>|null
     */
    public function findOwnedReview(int $reviewId, int $userId): ?array;

    /**
     * @param int $movieId
     *
     * @return bool
     */
    public function movieExists(int $movieId): bool;

    /**
     * @param int $ratingId
     * @param int $userId
     * @param int $movieId
     *
     * @return bool
     */
    public function ratingBelongsToUserMovie(int $ratingId, int $userId, int $movieId): bool;

    /**
     * @param int $userId
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function createReview(int $userId, array $attributes): array;

    /**
     * @param int $reviewId
     * @param int $userId
     * @param array<string, mixed> $attributes
     */
    public function updateReview(int $reviewId, int $userId, array $attributes): void;

    /**
     * @param int $reviewId
     * @param int $userId
     */
    public function softDeleteReview(int $reviewId, int $userId): void;
}
