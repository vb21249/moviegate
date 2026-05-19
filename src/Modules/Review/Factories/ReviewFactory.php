<?php

declare(strict_types=1);

namespace App\Modules\Review\Factories;

use App\Modules\Review\Entities\ReviewEntity;

/**
 * Review domain entity factory.
 */
final class ReviewFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): ReviewEntity
    {
        return new ReviewEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}
