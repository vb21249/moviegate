<?php

declare(strict_types=1);

namespace App\Common\Services;

use App\Common\Contracts\SearchServiceInterface;
use App\Common\Dto\PaginationDto;
use App\Common\Dto\SearchCriteriaDto;
use App\Common\Dto\SearchResultDto;

/**
 * Default search facade placeholder.
 */
final class SearchService implements SearchServiceInterface
{
    public function search(SearchCriteriaDto $criteria, PaginationDto $pagination): SearchResultDto
    {
        return new SearchResultDto(
            items: [],
            pagination: [
                'page' => $pagination->page,
                'per_page' => $pagination->perPage,
                'total' => 0,
            ]
        );
    }
}
