<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Data required to create a user account.
 */
final class CreateUserDto
{
    /**
     * @param string $email
     * @param string $username
     * @param string $passwordHash
     */
    public function __construct(
        public readonly string $email,
        public readonly string $username,
        public readonly string $passwordHash,
    ) {
    }
}
