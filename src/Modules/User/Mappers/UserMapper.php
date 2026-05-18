<?php

declare(strict_types=1);

namespace App\Modules\User\Mappers;

use App\Modules\User\DTO\UserDto;

/**
 * Maps persistence rows into user DTOs.
 */
final class UserMapper
{
    /**
     * @param array<string, mixed> $row
     * @param array<string, int> $stats
     *
     * @return UserDto
     */
    public function mapProfile(array $row, array $stats = []): UserDto
    {
        return new UserDto(
            id: (int) $row['id'],
            username: (string) $row['username'],
            avatarUrl: $row['avatar_url'] !== null ? (string) $row['avatar_url'] : null,
            bio: $row['bio'] !== null ? (string) $row['bio'] : null,
            status: (string) $row['status'],
            createdAt: $row['created_at'] !== null ? (string) $row['created_at'] : null,
            stats: $stats,
        );
    }
}
