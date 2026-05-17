<?php

declare(strict_types=1);

namespace App\Common\Contracts;

use App\Common\Dto\PaginationDto;
use App\Common\Dto\SearchCriteriaDto;
use App\Common\Dto\SearchResultDto;

/**
 * Search abstraction.
 */
interface SearchServiceInterface
{
    /**
     * @param SearchCriteriaDto $criteria
     * @param PaginationDto $pagination
     */
    public function search(SearchCriteriaDto $criteria, PaginationDto $pagination): SearchResultDto;
}
