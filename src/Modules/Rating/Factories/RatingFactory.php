<?php

declare(strict_types=1);

namespace App\Modules\Rating\Factories;

use App\Modules\Rating\Entities\RatingEntity;

/**
 * Rating domain entity factory.
 */
final class RatingFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): RatingEntity
    {
        return new RatingEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}
