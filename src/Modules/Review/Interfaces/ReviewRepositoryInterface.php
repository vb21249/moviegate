<?php

declare(strict_types=1);

namespace App\Modules\Review\Interfaces;

/**
 * Review repository contract.
 */
interface ReviewRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}