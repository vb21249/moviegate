<?php

declare(strict_types=1);

namespace App\Modules\Search\Factories;

use App\Modules\Search\Entities\SearchEntity;

/**
 * Search factory placeholder.
 */
final class SearchFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): SearchEntity
    {
        return new SearchEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}