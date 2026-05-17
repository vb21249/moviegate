<?php

declare(strict_types=1);

namespace App\Modules\Rating\Entities;

/**
 * Rating domain entity.
 */
final class RatingEntity
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