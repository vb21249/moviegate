<?php

declare(strict_types=1);

namespace App\Modules\Common\Factories;

use App\Modules\Common\Entities\CommonEntity;

/**
 * Common factory placeholder.
 */
final class CommonFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): CommonEntity
    {
        return new CommonEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}