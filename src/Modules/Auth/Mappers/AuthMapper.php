<?php

declare(strict_types=1);

namespace App\Modules\Auth\Mappers;

use App\Modules\Auth\DTO\AuthDto;
use App\Modules\Auth\Entities\AuthEntity;

/**
 * Auth mapper placeholder.
 */
final class AuthMapper
{
    public function mapToDto(AuthEntity $entity): AuthDto
    {
        return new AuthDto($entity->attributes);
    }
}