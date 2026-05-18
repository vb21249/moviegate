<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Access and refresh token pair returned to API clients.
 */
final class AuthTokenPairDto
{
    /**
     * @param string $accessToken
     * @param string $refreshToken
     * @param string $tokenType
     * @param int $expiresIn
     * @param int $refreshExpiresIn
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly string $refreshToken,
        public readonly string $tokenType,
        public readonly int $expiresIn,
        public readonly int $refreshExpiresIn,
    ) {
    }
}
