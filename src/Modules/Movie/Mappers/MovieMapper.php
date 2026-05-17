<?php

declare(strict_types=1);

namespace App\Modules\Movie\Mappers;

use App\Modules\Movie\DTO\MovieDto;
use App\Modules\Movie\Entities\MovieEntity;

/**
 * Movie mapper placeholder.
 */
final class MovieMapper
{
    public function mapToDto(MovieEntity $entity): MovieDto
    {
        return new MovieDto($entity->attributes);
    }
}