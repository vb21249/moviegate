<?php

declare(strict_types=1);

namespace App\Modules\Comment\Factories;

use App\Modules\Comment\Entities\CommentEntity;

/**
 * Comment factory placeholder.
 */
final class CommentFactory
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function make(array $attributes = []): CommentEntity
    {
        return new CommentEntity(
            id: $attributes['id'] ?? null,
            attributes: $attributes,
        );
    }
}