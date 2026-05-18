<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Signed action token used for email verification or password reset.
 */
final class AuthActionTokenDto
{
    /**
     * @param string $token
     * @param string $type
     * @param int $expiresIn
     */
    public function __construct(
        public readonly string $token,
        public readonly string $type,
        public readonly int $expiresIn,
    ) {
    }
}
