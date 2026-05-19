<?php

declare(strict_types=1);

namespace App\Modules\Feed\Factories;

use App\Modules\Feed\Entities\FeedEntity;

/**
 * Feed domain entity factory.
 */
final class FeedFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): FeedEntity
    {
        return new FeedEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}
