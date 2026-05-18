<?php

declare(strict_types=1);

namespace App\Modules\Auth\DTO;

/**
 * Aggregated auth payload containing current user and token pair.
 */
final class AuthDto
{
    /**
     * @param AuthUserDto $user
     * @param AuthTokenPairDto|null $tokens
     * @param AuthActionTokenDto|null $actionToken
     */
    public function __construct(
        public readonly AuthUserDto $user,
        public readonly ?AuthTokenPairDto $tokens = null,
        public readonly ?AuthActionTokenDto $actionToken = null,
    ) {
    }
}
