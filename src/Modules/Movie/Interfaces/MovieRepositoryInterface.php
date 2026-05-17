<?php

declare(strict_types=1);

namespace App\Modules\Movie\Interfaces;

/**
 * Movie repository contract.
 */
interface MovieRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}