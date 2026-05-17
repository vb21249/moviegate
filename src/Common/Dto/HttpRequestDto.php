<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Outbound HTTP request DTO.
 */
final class HttpRequestDto
{
    /**
     * @param string $method
     * @param string $uri
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $body
     */
    public function __construct(
        public readonly string $method,
        public readonly string $uri,
        public readonly array $headers = [],
        public readonly array $query = [],
        public readonly ?array $body = null,
    ) {
    }
}
