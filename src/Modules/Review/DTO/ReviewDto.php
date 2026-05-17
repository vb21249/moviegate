<?php

declare(strict_types=1);

namespace App\Modules\Review\DTO;

/**
 * Review DTO.
 */
final class ReviewDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}