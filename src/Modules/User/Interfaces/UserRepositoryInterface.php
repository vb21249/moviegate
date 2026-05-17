<?php

declare(strict_types=1);

namespace App\Modules\User\Interfaces;

/**
 * User repository contract.
 */
interface UserRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}