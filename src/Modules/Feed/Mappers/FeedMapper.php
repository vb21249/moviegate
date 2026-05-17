<?php

declare(strict_types=1);

namespace App\Modules\Feed\Mappers;

use App\Modules\Feed\DTO\FeedDto;
use App\Modules\Feed\Entities\FeedEntity;

/**
 * Feed mapper placeholder.
 */
final class FeedMapper
{
    public function mapToDto(FeedEntity $entity): FeedDto
    {
        return new FeedDto($entity->attributes);
    }
}