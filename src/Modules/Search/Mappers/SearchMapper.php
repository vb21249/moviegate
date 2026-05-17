<?php

declare(strict_types=1);

namespace App\Modules\Search\Mappers;

use App\Modules\Search\DTO\SearchDto;
use App\Modules\Search\Entities\SearchEntity;

/**
 * Search mapper placeholder.
 */
final class SearchMapper
{
    public function mapToDto(SearchEntity $entity): SearchDto
    {
        return new SearchDto($entity->attributes);
    }
}