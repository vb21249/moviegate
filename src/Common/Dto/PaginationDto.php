<?php

declare(strict_types=1);

namespace App\Common\Dto;

/**
 * Pagination request DTO.
 */
final class PaginationDto
{
    /**
     * @param int $page
     * @param int $perPage
     */
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }
}
