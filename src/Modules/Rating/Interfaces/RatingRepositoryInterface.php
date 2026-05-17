<?php

declare(strict_types=1);

namespace App\Modules\Rating\Interfaces;

/**
 * Rating repository contract.
 */
interface RatingRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}