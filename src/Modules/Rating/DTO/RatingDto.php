<?php

declare(strict_types=1);

namespace App\Modules\Rating\DTO;

/**
 * Rating DTO.
 */
final class RatingDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}