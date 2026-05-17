<?php

declare(strict_types=1);

namespace App\Modules\Review\Mappers;

use App\Modules\Review\DTO\ReviewDto;
use App\Modules\Review\Entities\ReviewEntity;

/**
 * Review mapper placeholder.
 */
final class ReviewMapper
{
    public function mapToDto(ReviewEntity $entity): ReviewDto
    {
        return new ReviewDto($entity->attributes);
    }
}