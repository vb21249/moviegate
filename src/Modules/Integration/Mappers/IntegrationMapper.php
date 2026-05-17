<?php

declare(strict_types=1);

namespace App\Modules\Integration\Mappers;

use App\Modules\Integration\DTO\IntegrationDto;
use App\Modules\Integration\Entities\IntegrationEntity;

/**
 * Integration mapper placeholder.
 */
final class IntegrationMapper
{
    public function mapToDto(IntegrationEntity $entity): IntegrationDto
    {
        return new IntegrationDto($entity->attributes);
    }
}