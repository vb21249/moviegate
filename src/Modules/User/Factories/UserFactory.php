<?php

declare(strict_types=1);

namespace App\Modules\User\Factories;

use App\Modules\User\Entities\UserEntity;

/**
 * User factory placeholder.
 */
final class UserFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): UserEntity
    {
        return new UserEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}