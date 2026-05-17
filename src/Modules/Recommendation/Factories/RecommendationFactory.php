<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Factories;

use App\Modules\Recommendation\Entities\RecommendationEntity;

/**
 * Recommendation factory placeholder.
 */
final class RecommendationFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): RecommendationEntity
    {
        return new RecommendationEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}