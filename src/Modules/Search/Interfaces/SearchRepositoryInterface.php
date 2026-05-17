<?php

declare(strict_types=1);

namespace App\Modules\Search\Interfaces;

/**
 * Search repository contract.
 */
interface SearchRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}