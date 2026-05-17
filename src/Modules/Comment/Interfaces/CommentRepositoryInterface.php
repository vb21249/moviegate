<?php

declare(strict_types=1);

namespace App\Modules\Comment\Interfaces;

/**
 * Comment repository contract.
 */
interface CommentRepositoryInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return list<array<string, mixed>>
     */
    public function query(array $criteria = []): array;
}