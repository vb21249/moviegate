<?php

declare(strict_types=1);

namespace App\Modules\Search\Adapters;

use App\Common\Dto\PaginationDto;
use App\Common\Dto\SearchCriteriaDto;
use App\Common\Dto\SearchResultDto;

/**
 * Database-backed search adapter placeholder.
 */
final class DatabaseSearchAdapter
{
    public function search(SearchCriteriaDto $criteria, PaginationDto $pagination): SearchResultDto
    {
        return new SearchResultDto(
            items: [],
            pagination: [
                'page' => $pagination->page,
                'per_page' => $pagination->perPage,
                'total' => 0,
                'filters' => $criteria->filters,
            ]
        );
    }
}
