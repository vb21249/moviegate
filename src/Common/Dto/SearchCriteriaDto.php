<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Search criteria DTO.
 */
final class SearchCriteriaDto
{
    /**
     * @param string $term
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public readonly string $term,
        public readonly array $filters = [],
    ) {
    }
}
