<?php

declare(strict_types=1);

namespace App\Modules\Auth\Factories;

use App\Modules\Auth\Entities\AuthEntity;

/**
 * Auth factory placeholder.
 */
final class AuthFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): AuthEntity
    {
        return new AuthEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}