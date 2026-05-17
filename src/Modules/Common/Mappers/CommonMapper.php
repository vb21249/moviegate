<?php

declare(strict_types=1);

namespace App\Modules\Common\Mappers;

use App\Modules\Common\DTO\CommonDto;
use App\Modules\Common\Entities\CommonEntity;

/**
 * Common mapper placeholder.
 */
final class CommonMapper
{
    public function mapToDto(CommonEntity $entity): CommonDto
    {
        return new CommonDto($entity->attributes);
    }
}