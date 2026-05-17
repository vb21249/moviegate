<?php

declare(strict_types=1);

namespace App\Modules\Feed\Interfaces;

/**
 * Feed repository contract.
 */
interface FeedRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}