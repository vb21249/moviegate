<?php

declare(strict_types=1);

namespace App\Modules\Search\Filters;

/**
 * Search filter DTO placeholder.
 */
final class SearchFilters
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public readonly array $filters = [],
    ) {
    }
}
