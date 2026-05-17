<?php

declare(strict_types=1);

namespace App\Modules\Review\Responses;

/**
 * Review response DTO.
 */
final class ReviewResponse
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}