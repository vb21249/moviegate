<?php

declare(strict_types=1);

namespace App\Modules\Common\Interfaces;

/**
 * Common repository contract.
 */
interface CommonRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}