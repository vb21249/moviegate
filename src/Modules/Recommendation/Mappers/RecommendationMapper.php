<?php

declare(strict_types=1);

namespace App\Modules\Recommendation\Mappers;

use App\Modules\Recommendation\DTO\RecommendationDto;
use App\Modules\Recommendation\Entities\RecommendationEntity;

/**
 * Recommendation mapper placeholder.
 */
final class RecommendationMapper
{
    public function mapToDto(RecommendationEntity $entity): RecommendationDto
    {
        return new RecommendationDto($entity->attributes);
    }
}