<?php

declare(strict_types=1);

namespace App\Modules\Auth\Entities;

/**
 * Authenticated session entity for domain-level reasoning.
 */
final class AuthEntity
{
    /**
     * @param int $userId
     * @param string $email
     * @param string $username
     * @param bool $emailVerified
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $email,
        public readonly string $username,
        public readonly bool $emailVerified,
    ) {
    }
}
