<?php

declare(strict_types=1);

namespace App\Modules\Comment\Interfaces;

/**
 * Comment repository contract.
 */
interface CommentRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findComments(
        int $limit,
        int $offset,
        ?int $movieId = null,
        ?int $reviewId = null,
        ?int $userId = null,
        ?int $parentId = null
    ): array;

    public function countComments(
        ?int $movieId = null,
        ?int $reviewId = null,
        ?int $userId = null,
        ?int $parentId = null
    ): int;

    /**
     * @return array<string, mixed>|null
     */
    public function findComment(int $commentId): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function findOwnedComment(int $commentId, int $userId): ?array;

    public function movieExists(int $movieId): bool;

    public function reviewExists(int $reviewId): bool;

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    public function createComment(int $userId, array $attributes): array;

    /**
     * @param array<string, mixed> $attributes
     */
    public function updateComment(int $commentId, int $userId, array $attributes): void;

    public function softDeleteComment(int $commentId, int $userId): void;

    /**
     * @return array<string, mixed>
     */
    public function likeComment(int $commentId, int $userId): array;
}
