<?php

declare(strict_types=1);

namespace App\Modules\Movie\DTO;

/**
 * Movie DTO.
 */
final class MovieDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}