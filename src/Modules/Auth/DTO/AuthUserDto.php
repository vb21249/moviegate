<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Public auth-facing user DTO.
 */
final class AuthUserDto
{
    /**
     * @param int $id
     * @param string $email
     * @param string $username
     * @param bool $emailVerified
     * @param string|null $avatarUrl
     * @param string|null $bio
     * @param string|null $lastLoginAt
     */
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $username,
        public readonly bool $emailVerified,
        public readonly ?string $avatarUrl = null,
        public readonly ?string $bio = null,
        public readonly ?string $lastLoginAt = null,
    ) {
    }
}
