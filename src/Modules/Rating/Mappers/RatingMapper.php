<?php

declare(strict_types=1);

namespace App\Modules\Rating\Mappers;

use App\Modules\Rating\DTO\RatingDto;
use App\Modules\Rating\Entities\RatingEntity;

/**
 * Rating mapper placeholder.
 */
final class RatingMapper
{
    public function mapToDto(RatingEntity $entity): RatingDto
    {
        return new RatingDto($entity->attributes);
    }
}