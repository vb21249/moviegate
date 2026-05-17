<?php

declare(strict_types=1);

namespace App\Modules\Integration\DTO;

/**
 * Integration DTO.
 */
final class IntegrationDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}