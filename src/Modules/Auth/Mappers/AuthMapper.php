<?php

declare(strict_types=1);

namespace App\Modules\Auth\Mappers;

use App\Modules\Auth\DTO\AuthUserDto;
use App\Modules\User\Models\UserRecord;

/**
 * Maps user persistence model to auth-facing DTO.
 */
final class AuthMapper
{
    /**
     * @param UserRecord $user
     *
     * @return AuthUserDto
     */
    public function toUserDto(UserRecord $user): AuthUserDto
    {
        return new AuthUserDto(
            id: (int) $user->id,
            email: (string) $user->email,
            username: (string) $user->username,
            emailVerified: $user->email_verified_at !== null,
            avatarUrl: $user->avatar_url !== null ? (string) $user->avatar_url : null,
            bio: $user->bio !== null ? (string) $user->bio : null,
            lastLoginAt: $user->last_login_at !== null ? (string) $user->last_login_at : null,
        );
    }
}
