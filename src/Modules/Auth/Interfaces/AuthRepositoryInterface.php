<?php

declare(strict_types=1);

namespace App\Modules\Auth\Interfaces;

/**
 * Auth repository contract.
 */
interface AuthRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}