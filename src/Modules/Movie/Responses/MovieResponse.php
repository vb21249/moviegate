<?php

declare(strict_types=1);

namespace App\Modules\Movie\Responses;

/**
 * Movie response DTO with module-level payload shape.
 */
final class MovieResponse
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
