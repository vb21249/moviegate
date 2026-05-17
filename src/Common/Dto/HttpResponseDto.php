<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Outbound HTTP response DTO.
 */
final class HttpResponseDto
{
    /**
     * @param int $statusCode
     * @param array<string, string> $headers
     * @param array<string, mixed>|string|null $body
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly array $headers = [],
        public readonly array|string|null $body = null,
    ) {
    }
}
