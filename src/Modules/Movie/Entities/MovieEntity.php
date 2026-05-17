<?php

declare(strict_types=1);

namespace App\Modules\Movie\Entities;

/**
 * Movie domain entity.
 */
final class MovieEntity
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