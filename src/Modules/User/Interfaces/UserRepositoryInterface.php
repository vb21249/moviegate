<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces;

/**
 * User repository contract.
 */
interface UserRepositoryInterface
{
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return list<array<string, mixed>>
     */
    public function findPublicProfiles(int $limit, int $offset): array;

    /**
     * @return int
     */
    public function countPublicProfiles(): int;

    /**
     * @param int $userId
     *
     * @return array<string, mixed>|null
     */
    public function findPublicProfile(int $userId): ?array;

    /**
     * @param int $userId
     *
     * @return array<string, int>
     */
    public function getProfileStats(int $userId): array;

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return list<array<string, mixed>>
     */
    public function findActivity(int $userId, int $limit, int $offset): array;

    /**
     * @param int $userId
     *
     * @return int
     */
    public function countActivity(int $userId): int;

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return list<array<string, mixed>>
     */
    public function findRecentRatings(int $userId, int $limit, int $offset): array;

    /**
     * @param int $userId
     *
     * @return int
     */
    public function countRecentRatings(int $userId): int;

    /**
     * @param int $userId
     * @param int $limit
     * @param int $offset
     *
     * @return list<array<string, mixed>>
     */
    public function findPosts(int $userId, int $limit, int $offset): array;

    /**
     * @param int $userId
     *
     * @return int
     */
    public function countPosts(int $userId): int;
}
