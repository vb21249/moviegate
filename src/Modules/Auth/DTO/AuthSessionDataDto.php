<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Data required to persist a refresh-token based session.
 */
final class AuthSessionDataDto
{
    /**
     * @param int $userId
     * @param string $refreshToken
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param string $expiresAt
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $refreshToken,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly string $expiresAt,
    ) {
    }
}
