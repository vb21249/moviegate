<?php

declare(strict_types=1);

namespace App\Modules\Auth\Factories;

use App\Modules\Auth\DTO\AuthDto;
use App\Modules\Auth\DTO\AuthTokenPairDto;
use App\Modules\Auth\DTO\AuthUserDto;

/**
 * Small factory for assembling auth aggregate DTOs.
 */
final class AuthFactory
{
    /**
     * @param AuthUserDto $user
     * @param AuthTokenPairDto|null $tokens
     *
     * @return AuthDto
     */
    public function make(AuthUserDto $user, ?AuthTokenPairDto $tokens = null): AuthDto
    {
        return new AuthDto($user, $tokens);
    }
}
