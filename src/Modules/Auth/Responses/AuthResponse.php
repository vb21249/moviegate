<?php

declare(strict_types=1);

namespace App\Modules\Auth\Responses;

use App\Modules\Auth\DTO\AuthActionTokenDto;
use App\Modules\Auth\DTO\AuthTokenPairDto;
use App\Modules\Auth\DTO\AuthUserDto;

/**
 * Auth response DTO.
 */
final class AuthResponse
{
    /**
     * @param string $message
     * @param AuthUserDto|null $user
     * @param AuthTokenPairDto|null $tokens
     * @param AuthActionTokenDto|null $actionToken
     */
    public function __construct(
        public readonly string $message,
        public readonly ?AuthUserDto $user = null,
        public readonly ?AuthTokenPairDto $tokens = null,
        public readonly ?AuthActionTokenDto $actionToken = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'message' => $this->message,
            'user' => $this->user !== null ? [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'username' => $this->user->username,
                'email_verified' => $this->user->emailVerified,
                'avatar_url' => $this->user->avatarUrl,
                'bio' => $this->user->bio,
                'last_login_at' => $this->user->lastLoginAt,
            ] : null,
            'tokens' => $this->tokens !== null ? [
                'access_token' => $this->tokens->accessToken,
                'refresh_token' => $this->tokens->refreshToken,
                'token_type' => $this->tokens->tokenType,
                'expires_in' => $this->tokens->expiresIn,
                'refresh_expires_in' => $this->tokens->refreshExpiresIn,
            ] : null,
            'action_token' => $this->actionToken !== null ? [
                'token' => $this->actionToken->token,
                'type' => $this->actionToken->type,
                'expires_in' => $this->actionToken->expiresIn,
            ] : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
