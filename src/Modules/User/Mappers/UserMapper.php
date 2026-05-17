<?php

declare(strict_types=1);

namespace App\Modules\User\Mappers;

use App\Modules\User\DTO\UserDto;
use App\Modules\User\Entities\UserEntity;

/**
 * User mapper placeholder.
 */
final class UserMapper
{
    public function mapToDto(UserEntity $entity): UserDto
    {
        return new UserDto($entity->attributes);
    }
}