<?php

declare(strict_types=1);

namespace App\Modules\Movie\Factories;

use App\Modules\Movie\Entities\MovieEntity;

/**
 * Movie factory placeholder.
 */
final class MovieFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): MovieEntity
    {
        return new MovieEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}