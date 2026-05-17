<?php

declare(strict_types=1);

namespace App\Modules\Comment\Entities;

/**
 * Comment domain entity.
 */
final class CommentEntity
{
    /**
     * @param int|string|null $id
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        public readonly int|string|null $id = null,
        public readonly array $attributes = [],
    ) {
    }
}