<?php

declare(strict_types=1);

namespace App\Modules\Review\Entities;

/**
 * Review domain entity.
 */
final class ReviewEntity
{
    /**
     * @param int|string|null $id
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly int|string|null $id = null,
        public readonly array $attributes = [],
    ) {
    }
}