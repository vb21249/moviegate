<?php

declare(strict_types=1);

namespace App\Modules\Search\Responses;

/**
 * Search response DTO.
 */
final class SearchResponse
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