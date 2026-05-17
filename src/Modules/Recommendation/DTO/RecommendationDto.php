<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\DTO;

/**
 * Recommendation DTO.
 */
final class RecommendationDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}