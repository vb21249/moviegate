<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Interfaces;

/**
 * Recommendation repository contract.
 */
interface RecommendationRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}