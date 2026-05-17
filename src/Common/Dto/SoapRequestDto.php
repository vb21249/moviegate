<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * SOAP request DTO.
 */
final class SoapRequestDto
{
    /**
     * @param string $operation
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly string $operation,
        public readonly array $payload = [],
        public readonly array $options = [],
    ) {
    }
}
