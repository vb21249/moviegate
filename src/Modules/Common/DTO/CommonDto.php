<?php

declare(strict_types=1);

namespace App\Modules\Common\DTO;

/**
 * Common DTO.
 */
final class CommonDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}