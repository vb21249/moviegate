<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Interfaces;

/**
 * Recommendation repository contract.
 */
interface RecommendationRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function findPopularMovies(int $limit, ?int $excludeUserId = null): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findRecentMovies(int $limit, ?int $excludeUserId = null): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function findPersonalizedMovies(int $userId, int $limit): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findFreshCache(int $userId, string $source): ?array;

    /**
     * @param array<string, mixed>|list<mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function storeCache(int $userId, string $source, array $payload, int $ttlSeconds): array;
}
