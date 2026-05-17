<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * SOAP response DTO.
 */
final class SoapResponseDto
{
    /**
     * @param mixed $body
     */
    public function __construct(
        public readonly mixed $body,
    ) {
    }
}
