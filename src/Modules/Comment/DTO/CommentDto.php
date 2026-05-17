<?php

declare(strict_types=1);

namespace App\Modules\Comment\DTO;

/**
 * Comment DTO.
 */
final class CommentDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}