<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Auth DTO.
 */
final class AuthDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}