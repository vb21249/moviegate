<?php

declare(strict_types=1);

namespace App\Modules\Integration\Interfaces;

/**
 * Integration repository contract.
 */
interface IntegrationRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}