<?php

declare(strict_types=1);

namespace App\Modules\Search\DTO;

/**
 * Search DTO.
 */
final class SearchDto
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload = [],
    ) {
    }
}