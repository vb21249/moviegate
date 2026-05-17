<?php

declare(strict_types=1);

namespace App\Modules\Integration\Factories;

use App\Modules\Integration\Entities\IntegrationEntity;

/**
 * Integration factory placeholder.
 */
final class IntegrationFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): IntegrationEntity
    {
        return new IntegrationEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}