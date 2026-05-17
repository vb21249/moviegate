<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Search result DTO.
 */
final class SearchResultDto
{
    /**
     * @param list<array<string, mixed>> $items
     * @param array<string, mixed> $pagination
     */
    public function __construct(
        public readonly array $items,
        public readonly array $pagination,
    ) {
    }
}
